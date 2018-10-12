<?php

/**
 * quickcheck Module
 *
 * The quickcheck module is a module for creating quizzes. It is attachable.
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      2.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\Core\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use SecurityUtil;
use ModUtil;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Paustian\QuickcheckModule\Form\TFQuestion;
use Paustian\QuickcheckModule\Form\TextQuestion;
use Paustian\QuickcheckModule\Form\MCQuestion;
use Paustian\QuickcheckModule\Form\MAnsQuestion;
use Paustian\QuickcheckModule\Form\MatchQuestion;
use Paustian\QuickcheckModule\Form\ImportText;
use Paustian\QuickcheckModule\Form\ExamForm;
use Paustian\QuickcheckModule\Form\ExportForm;
use Paustian\QuickcheckModule\Form\CategorizeForm;

/**
 * The various types of questions. We use defines to make the code
 * easier to read
 */

/**
 * @Route("/admin")
 *
 * Administrative controllers for the quickcheck module
 */
class AdminController extends AbstractController {

    const _QUICKCHECK_TEXT_TYPE = 0;
    const _QUICKCHECK_MULTIPLECHOICE_TYPE = 1;
    const _QUICKCHECK_TF_TYPE = 2;
    const _QUICKCHECK_MATCHING_TYPE = 3;
    const _QUICKCHECK_MULTIANSWER_TYPE = 4;

    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize() {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * @Route("")
     * 
     */
    public function indexAction(Request $request) {

        // Return a page of menu items.
        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_menu.html.twig');
    }

    /**
     * @Route("/edit/{exam}")
     * 
     * * form to add new exam  
     *
     * Create a new exam
     *
     * @author       Timothy Paustian
     * @return       The form for creating a new exam response object
     */
    public function editAction(Request $request, QuickcheckExamEntity $exam = null) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $questions = null;
        if ($exam !== null) {
            $questions = $this->_prep_question_list('checkbox', $exam->getQuickcheckquestions());
        } else {
            $questions = $this->_prep_question_list('checkbox');
        }
        //If the $questions already exists coming in, then we want to merge
        //if it doesn't we need to persist it instead.
        $doMerge = false;
        if (null === $exam) {
            $exam = new QuickcheckExamEntity();
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(ExamForm::class, $exam);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $questPick = $request->get('questions');
            $exam->setQuickcheckquestions($questPick);
            if ($doMerge) {
                $em->merge($exam);
            } else {
                $exam->setQuickcheckrefid(0);
                $em->persist($exam);
            }
            $em->flush();
            $this->addFlash('status', $this->__('Exam saved.'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_exam.html.twig', array(
                    'form' => $form->createView(), 'questions' => $questions
        ));
    }

    /**
     * @Route ("/delete/{exam}")
     * 
     *  deleteAction - delete the exam.
     * 
     * @param Request $request
     */
    public function deleteAction(Request $request, QuickcheckExamEntity $exam = null) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_DELETE)) {
            return DataUtil::formatForDisplayHTML($this->__("You do not have permission to delete exams."));
        }
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_modify'));
        if ($exam == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($exam);
        $em->flush();
        $this->addFlash('status', $this->__('Exam Deleted.'));
        return $response;
    }

    /**
     * @Route ("/deletequestion/{question}")
     * deleteQuestionAction - delete the question.
     * 
     * @param Request $request
     * @param QuickcheckQuestionEntity $question
     * 
     */
    public function deleteQuestionAction(Request $request, QuickcheckQuestionEntity $question = null) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_DELETE)) {
            return DataUtil::formatForDisplayHTML($this->__("You do not have permission to delete questions."));
        }
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
        if ($question == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $id = $question->getId();
        $this->_removeQuestionFromExams($em, $id);
        $em->remove($question);
        $em->flush();
        $this->addFlash('status', $this->__('Question Deleted.'));
        return $response;
    }

    /**
     * remove a deleted question from an exam
     * @param  $id the id of the question to delete
     * @param  $em the entity manager   
     * @return true if successful
     */
    private function _removeQuestionFromExams($em, $id) {
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        // execute query
        $exams = $query->getResult();
        foreach ($exams as $exam) {
            $questions = $exam->getQuickcheckquestions();
            $q_index = array_search($id, $questions);
            //we have to be careful here and use boolean operators
            //$q_index can be 0
            if ($q_index === FALSE) {
                continue;
            }
            //if we got here, the quesiton is part of the array
            //remove the item
            unset($questions[$q_index]);
            //we need to copy this over again to reset the index. May not be necessary in 
            //this case, but it's nicer to have a continuous index of values.
            $questions = array_values($questions);
            $exam->setQuickcheckquestions($questions);
            $em->merge($exam);
        }
        $em->flush();
        return true;
    }

    /**
     * @Route ("/modify")
     * 
     * modify an exam
     *
     * Set up a form to present all the exams and let the user choose
     * The one to modify
     */
    public function modifyAction(Request $request) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            return DataUtil::formatForDisplayHTML($this->__("You do not have permission to edit questions."));
        }
        // create a QueryBuilder instance
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $exams = $query->getResult();
        if (!$exams) {
            $this->addFlash('error', $this->__('There are no exams to edit'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }
        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_modify.html.twig', ['exams' => $exams]);
    }

    /**
     * @Route ("/attach/")
     * @Method("POST")
     * @param Request $request
     * 
     * Parameters of the request are:
     *  ret_url the URL to return to after being done
     *  art_id the article Id of the item that the exam is being attached to
     *  exam the exam that is being attached to the article
     * 
     * @return RedirectResponse
     */
    public function attachAction(Request $request) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        //get the values
        $ret_url = $request->request->get('return_url', null);
        $exam = $request->request->get('exam', null);
        $art_id = $request->request->get('art_id', null);
        $attach = $request->request->get('attach', null);
        //arguments check
        if ($ret_url == "") {
            //if a default route is not provided, then just send them to the quickcheck admin interface
            $ret_url = $this->generateUrl('paustianquickcheckmodule_admin_index');
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');

        //get rid of the old exam if there is one.    
        $old_exam = $repo->get_exam($art_id);
        if ($old_exam) {
            $old_exam->setQuickcheckrefid(-1); //no article attached
            $em->merge($old_exam);
        }
        if (isset($attach)) {
            if (!isset($art_id) || !isset($exam)) {
                throw new NotFoundHttpException($this->__('An article id or exam id are missing'));
            }
            //modify the exam by grabbing it and then changing or adding the art_id
            $new_exam = $em->find('PaustianQuickcheckModule:QuickcheckExamEntity', $exam);
            if (null === $new_exam) {
                throw new \Doctrine\ORM\NoResultException($this->__('An exam was not found, when it should have been.'));
            }
            $new_exam->setQuickcheckrefid($art_id);
            $em->merge($new_exam);
            $request->getSession()->getFlashBag()->add('status', $this->__('The exam was attached.'));
        } else {
            $request->getSession()->getFlashBag()->add('status', $this->__('The exam was removed.'));
        }
        $em->flush();
        //finally return to the page that called.
        return new RedirectResponse($ret_url);
    }

    /**
     * _build_question_list.
     * @return array. Listing the text, id and type of each question and the
     * category it is in.
     * 
     */
    private function _build_questions_list($ckList = null) {
        $em = $this->getDoctrine()->getManager();
        // create a QueryBuilder instance
        $qb = $em->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        // execute query
        $items = $query->getResult();
        if (!$items) {
            return false;
        }

        $questions = array();
        $doCkList = isset($ckList);

        foreach ($items as $question) {
            //create the correct array for the chosen ID's to pass through correctly.
            $built_question['text'] = $question->getQuickcheckqText();
            $built_question['id'] = $question->getId();
            if ($doCkList) {
                $built_question['ck'] = in_array($built_question['id'], $ckList);
            } else {
                $built_question['ck'] = false;
            }

            $built_question['type'] = $question->getQuickcheckqType();
            //this sends back an ArrayCollection of 1 item (each question can only be in 1 category)
            $catItems = $question->getCategories();
            if ($catItems->isEmpty()) {
                $questions[$this->__('Uncategorized')][] = $built_question;
                continue;
            }

            //$catCollection is an ArrayCollection, we can use those calls
            $catObj = $catItems->first();
            //now we have a categoryEntity Object. What we want is the name.
            $catName = $catObj->getCategory()->getName();
            $questions[$catName][] = $built_question;
        }

        return $questions;
    }

    /**
     * _persistQustion save a question to the databse, set the announcement and then save.
     * 
     * @param type $question the question entity to save to the database
     * @param type $doMerge whether this is a merge (edit) or persist (new)
     * @param type $flashText the text to put in the flash area
     * @param type $redirect what url to redirect to
     * 
     * @return Response
     */
    private function _persistQuestion($question, $doMerge, $flashText, $redirect) {
        $em = $this->getDoctrine()->getManager();
        if ($doMerge) {
            $em->merge($question);
        } else {
            $em->persist($question);
        }
        
        $em->flush();
        $id = $question->getId();
        
        $this->addFlash('status', $flashText . " Question ID was: $id");
        return $redirect;
    }

    /**
     * _persistQuestionList - save back a list of questions after their categories
     * have changed.
     * @param type $questionList - the list of questions to save
     */
    private function _persistQuestionList($questionList, $categories) {
        $em = $this->getDoctrine()->getManager();
        $catElement = $categories->first();
        
        foreach ($questionList as $qId) {
            $question = $em->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $qId);
            //You need to close the element so that each question has its own link pointing from
            //the questionentity to the category table.
            $newCat = clone $catElement;
            $newCat['entity'] = $question;
            $categories->set(0, $newCat);
            $question->setCategories($categories);
            $em->merge($question);
        }
        $em->flush();
    }

    /**
     * @Route("/edittextquest/{question}")
     * form to add new text question
     *
     * Create a new quick_check question
     *
     * @param Request $request
     * @param QuickcheckQuestionEntity $question
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editTextQuestAction(Request $request, QuickcheckQuestionEntity $question = null) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        //If the $questions already exists coming in, then we want to merge
        //if it doesn't we need to persist it instead.
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(TextQuestion::class, $question);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fromModifyForm = $request->query->get('modify');
            $response = null;
            if ($fromModifyForm) {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
            } else {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_edittextquest'));
            }
            return $this->_persistQuestion($question, $doMerge, $this->__('Text question saved!'), $response);
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_new_text_question.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * 
     * @Route("/editmatchquest/{question}")
     * 
     * form to add new matching question
     *
     * Create a new quick_check question
     *
     * @author       Timothy Paustian
     * @return       Response
     */
    public function editMatchQuestAction(Request $request, QuickcheckQuestionEntity $question = null) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(MatchQuestion::class, $question);

        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $fromModifyForm = $request->query->get('modify');
            $response = null;
            if ($fromModifyForm) {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
            } else {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editmatchquest'));
            }
            return $this->_persistQuestion($question, $doMerge, $this->__('Matching question saved!'), $response);
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_new_match_question.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edittfquest/{question}")
     * form to add new TF question
     *
     * Create a new quick_check question
     *
     * @author       Timothy Paustian
     *  
     * @param Request $request
     * @param QuickcheckQuestionEntity $question
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editTFQuestAction(Request $request, QuickcheckQuestionEntity $question = null) {

        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(TFQuestion::class, $question);

        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        if ($form->isValid()) {
            $fromModifyForm = $request->query->get('modify');
            $response = null;
            if ($fromModifyForm) {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
            } else {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_edittfquest'));
            }
            return $this->_persistQuestion($question, $doMerge, $this->__('True/False question saved!'), $response);
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_new_tf_question.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/editmcquest/{question}")
     * 
     * Form to add a new multiple choice question
     * @param array $args
     * @return Response
     * December 16, 2015 - I could not upgrade this because symfony (the form engine part) has a bug in it that
     * relates to collectionTypes. I will have to wait until Symfony >2.7 is used in Zikula
     * 
     * Test data
     * HEre is an answer:100
     * HEre is another:0
     */
    public function editMCQuestAction(Request $request, QuickcheckQuestionEntity $question = null) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(MCQuestion::class, $question);

        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $fromModifyForm = $request->query->get('modify');
            $response = null;
            if ($fromModifyForm) {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
            } else {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editmcquest'));
            }
            return $this->_persistQuestion($question, $doMerge, $this->__('Multiple-Choice  question saved!'), $response);
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_new_mc_question.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route ("/editmansquest/{question}")
     * @param   $request the requst coming in
     * @param   $question the potential quesiton to edit.
     *  
     * form to add new multiple answer question
     *
     * edit a multiple answer question
     *
     * @author       Timothy Paustian
     * @return       The form for creating a new multiple answer question
     */
    public function editMANSQuestAction(Request $request, QuickcheckQuestionEntity $question = null) {

        if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(MAnsQuestion::class, $question);

        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $fromModifyForm = $request->query->get('modify');
            $response = null;
            if ($fromModifyForm) {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
            } else {
                $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editmansquest'));
            }
            return $this->_persistQuestion($question, $doMerge, $this->__('Multiple-Answer question saved!'), $response);
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_new_mans_question.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route ("/modifyquestion")
     * 
     * modifyquestion
     * 
     * Take the form data from editquestionsAction and dispatch it to the right question interface.
     * 
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function modifyquestionAction(Request $request) {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $id = $request->request->get('questions', null);
        $redirect_url = $this->get('router')->generate('paustianquickcheckmodule_admin_editquestions', array(), RouterInterface::ABSOLUTE_URL);

        if (!isset($id) || !is_numeric($id)) {
            $request->getSession()->getFlashBag()->add('status', $this->__("You need to pick a question"));
            return new RedirectResponse($redirect_url);
        }
        //grab the question
        $item = $this->getDoctrine()->getManager()->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $id);
        if (!$item) {
            $request->getSession()->getFlashBag()->add('status', $this->__("A question with that id does not exist"));
            return new RedirectResponse($redirect_url);
        }

        $questionType = $item->getQuickcheckqType();
        $button = $request->request->get('edit');
        if (!isset($button)) {
            $button = $request->request->get('delete', null);
        }
        $response = null;
        if ($button == 'edit') {
            switch ($questionType) {
                case self::_QUICKCHECK_TEXT_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_edittextquest', array('question' => $id, 'modify' => true)));
                    break;
                case self::_QUICKCHECK_MATCHING_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_editmatchquest', array('question' => $id, 'modify' => true)));
                    ;
                    break;
                case self::_QUICKCHECK_MULTIANSWER_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_editmansquest', array('question' => $id, 'modify' => true)));
                    ;
                    break;
                case self::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_editmcquest', array('question' => $id, 'modify' => true)));
                    ;
                    break;
                case self::_QUICKCHECK_TF_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_edittfquest', array('question' => $id, 'modify' => true)));
                    ;
                    break;
            }
        } else if ($button == 'delete') {
            return $this->deleteQuestionAction($request, $item);
        }
        return $response;
    }

    /**
     * @Route("/editquestions")
     * 
     * edit questions
     *
     *  This is the interface for modifying and deleting questions. I combined the
     *  two to make it more accessible to the user. The function displays a list of
     *  questions, and from there a user can edit or delete a question.
     * 
     * @return Response
     * @throws AccessDeniedException
     * 
     */
    public function editquestionsAction(Request $request) {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $questions = $this->_prep_question_list('radio');

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_editquestions.html.twig', array('questions' => $questions));
    }

    /**
     * _prep_question_list
     * 
     * returns either just a list of question names, or a
     * form array of questions. From there you can render how you please in a response
     * I may just want it to return the array (could just get rid of this funciton
     * and then add it as part of a form. I will have to see.
     * 
     * @param type $buttons
     * @return html
     */
    private function _prep_question_list($buttons = 'radio', $ckquestions = null) {

        $questions = $this->_build_questions_list($ckquestions);

        if (!$questions) {
            $this->addFlash('error', $this->__('There are no questions to modify. Create some first.'));

            return "";
        }

        $html = $this->renderView('PaustianQuickcheckModule:Admin:quickcheck_admin_qpart.html.twig', [ 'questions' => $questions,
            'buttons' => $buttons]);


        return $html;
    }

    /**
     * 
     * @Route("/manageexams")
     * @Method("POST")
     * 
     * @param Request $request
     * 
     * Manage exams
     *
     * This is the hook funciton that attaches/deletes/modifies an exam, with
     * questions, to another module. This is the interface that takes care of
     * those functions. What gets passed in, may be the id of the exam. If not
     * then we display an interface for choosing questions for the exam.
     * 
     * 
     */
    public function manageexamsAction(Request $request) {
        $art_id = $request->request->get('objectid', null);
        $ret_url = $request->request->get('extrainfo', null);
        $module = $request->request->get('extrainfo', null);
        $ret_text = "";
        $exam = modUtil::apiFunc('PaustianQuickcheckModule', 'user', 'get', array('art_id' => $art_id));

        if ($exam) {
            $ret_text = ModUtil::func('PaustianQuickcheckModule', 'user', 'display', array('exam' => $exam, 'returnurl' => $ret_url));
            $render = $this->view;
            $render->assign('hasexam', 1);
        }
        //no exam, display an interface to pick one, only if this is an admin
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            //we return an empty results for someone who cannot edit, you don't want to fail here
            //as each hooked page would then have the error message.
            return $ret_text;
        } else {
            $ret_text .= "<hr />" . ModUtil::func('PaustianQuickcheckModule', 'admin', 'pickquestions', array('returnurl' => $ret_url, 'art_id' => $art_id));
        }
        return $ret_text;
    }

    /**
     * @Route("/categorize")
     * 
     * Present an interface for puttin uncategorized quesitons into categories
     * 
     * @param Request $request
     * @return Response
     */
    public function categorizeAction(Request $request) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $questions = $this->_prep_question_list('checkbox');

        $form = $this->createForm(CategorizeForm::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $questPick = $request->get('questions');

            $categories = $form->get('categories')->getData();
            $this->_persistQuestionList($questPick, $categories);
            $this->addFlash('status', $this->__('Questions recategorized.'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_categorize'));
            return $response;
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_categorize.html.twig', ['form' => $form->createView(), 'questions' => $questions]);
    }

    /**
     * @Route("/addtocategory")
     * @Method("POST")
     * 
     * Take the category that was recorded and  add it to the selected questions
     * 
     * @param Request $request
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function addtocategoryAction(Request $request) {
        // Confirm authorisation code.
        //security check
        $this->checkCsrfToken();

        //you have to have edit access to do this
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        //get the questions
        $questions = $request->request->get('questions', null);
        //get the category
        $cat = $request->request->get('quickcheck_quest', null);
        foreach ($questions as $the_question) {
            $item = $this->getDoctrine()->getManager()->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $the_question);
            $item['__CATEGORIES__'] = $cat['__CATEGORIES__'];

            if (!modUtil::apiFunc('PaustianQuickcheckModule', 'admin', 'updatequestion', $item)) {
                return LogUtil::registerError("Update in category failed.");
            }
        }
        //if we have gotten here, we were successful
        $request->getSession()->getFlashBag()->add('status', $this->__('Categories updated.'));
        return new RedirectResponse(ModUtil::url('PaustianQuickcheckModule', 'admin', 'categorize'));
    }

    /**
     * @Route("/findunanswered")
     * 
     * findunanswered
     *
     * This is a quick function to find all the unexplained questions in the module
     * It's really a hack and isn't something you should be calling, I am just using
     * it for clean up of some previous data. It will likely go away in a future release
     *
     *  @return Response
     *  @throws AccessDeniedException
     */
    public function findunansweredAction() {
        //you have to have edit access to do this
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u')
                ->where('(u.quickcheckqexpan = ?1 OR u.quickcheckqexpan = ?2)')
                ->setParameters(array(1 => 'NULL',
                    2 => ''));
        $query = $qb->getQuery();

        $questions = $query->getResult();
//        $questions = array();
//        //copy the relevant data into an array
//        //Because of the way I designed the class, the getter method names don't match the variable.
//        foreach($item as $items){
//            
//        }
        if (empty($questions)) {
            $this->addFlash('error', $this->__("There are no unexplained questions."));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_findunanswered.html.twig', ['count' => count($questions), 'questions' => $questions]);
    }

    /**
     * 
     * @param type $xmlQuestionText - the xml text to parse
     * @param type $category - the categories to assign it.
     * 
     */
    private function _parseImportedQuizXML($xmlQuestionText, $category) {
        
        
        //An awesome function for parsing simple xml.
        //First we need to scrumb out our xml tags before doing this magic
        $tagsSearch1 = ["|<questiondoc>|", "|</questiondoc>|", 
                        "", "|</question>|",
                        "|<qtext>|", "|</qtext>|", 
                        "|<qanswer>|", "|</qanswer>|", 
                        "|<qexplanation>|", "|</qexplanation>|",
                        "|<qparam>|", "|</qparam>|", 
                        "|<qtype>|", "|</qtype>|", "|<\?xml(.*?)>|"];
        
        preg_match_all("|<question>(.*?)</question>|s", $xmlQuestionText, $questionArray);

//grab the manager for saving the data.
        $em = $this->getDoctrine()->getManager();
        foreach ($questionArray[1] as $q_item) {
            $doMerge = false;
            if(preg_match("|<qId>(.*?)</qId>|", $q_item, $qId)){
                $id = $qId[1];
            } else {
                $id=-1;
            }
            $question = null;
            if ($id < 0) {
                $question = new QuickcheckQuestionEntity();
            } else {
                $fquestion = $em->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $id);
                if ($fquestion === null) {
                    $question = new QuickcheckQuestionEntity();
                    $question->setId($id);
                } else {
                    $question = $fquestion;
                    $doMerge = true;
                }
            }
            
            if(!preg_match("|<qtype>(.*?)<\/qtype>|", $q_item, $type)){
                continue;
            }
            
            switch ($type[1]) {
                case 'multichoice':
                    $question->setQuickcheckqType(self::_QUICKCHECK_MULTIPLECHOICE_TYPE);
                    break;
                case 'text':
                    $question->setQuickcheckqType(self::_QUICKCHECK_TEXT_TYPE);
                    break;
                case 'multianswer':
                    $question->setQuickcheckqType(self::_QUICKCHECK_MULTIANSWER_TYPE);
                    break;
                case 'matching':
                    $question->setQuickcheckqType(self::_QUICKCHECK_MATCHING_TYPE);
                    break;
                case 'truefalse':
                    $question->setQuickcheckqType(self::_QUICKCHECK_TF_TYPE);
                    break;
                default:
                    //if we get here there is an issue, throw an error
                    throw new NotFoundHttpException($this->__('Unrecognized question type, was your qtype empty in the xml file?'));
                    break;
            }
            
            if(!preg_match("|<qtext>(.*?)<\/qtext>|s", $q_item, $text)){
                continue;
            }
            
            $question->setQuickcheckqText($text[1]);
            if(preg_match("|<qexplanation>(.*?)<\/qexplanation>|s", $q_item, $explan)){
                $question->setQuickcheckqExpan($explan[1]);
            } else {
                $question->setQuickcheckqExpan("");
            }
            
            preg_match("|<qparam>(.*?)<\/qparam>|s", $q_item, $sparam);
            
            if(!preg_match("|<qanswer>(.*?)<\/qanswer>|s", $q_item, $sanswer)){
                continue;
            }
            
            $answer = "";
            
            if (!empty($sparam)) {
                $q_param = explode('|', $sparam[1]);
                $q_answer = explode('|', $sanswer[1]);
                foreach ($q_param as $index => $param) {
                    $answer .= $q_answer[$index] . "|" . $param . "\n";
                }
            } else {
                if (strcmp($type[0], 'truefalse') == 0) {
                    if (strcmp($sanswer[1], 'False') == 0) {
                        $answer = 'no';
                    } else if (strcmp($sanswer[1], 'True') == 0) {
                        $answer = 'yes';
                    }
                }
            }
            
            if ($answer == '') {
                $answer = $sanswer[1];
            }
            
            $question->setQuickcheckqAnswer($answer);

            $question->setCategories($category);
            if ($doMerge) {
                $em->merge($question);
            } else {
                $em->persist($question);
            }
        }
        $em->flush();
    }

    /**
     * @Route("/importquiz")
     * 
     * set up the interface to import an xml file of quiz questions.
     * @return Response
     * @throws AccessDeniedException
     */
    public function importquizAction(Request $request) {
        //you have to have edit access to do this
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ImportText::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $xmlQuestionText = $form->get('importText')->getData();
            $category = $form->get('categories')->getData();
            //take the xml that is imported, and parse it into an array
            //That array should have filled out a new question entity which it shoudl return
            $questions = $this->_parseImportedQuizXML($xmlQuestionText, $category);
            $this->addFlash('status', $this->__("Questions imported."));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_importquiz'));
            return $response;
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_import.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/exportquiz")
     * 
     * export the chosen questions. First step. This displays the interface to export the questions
     * @return Response
     * @throws AccessDeniedException
     */
    public function exportquizAction(Request $request) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $questions = $this->_prep_question_list('checkbox');

        $form = $this->createForm(ExportForm::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            //I need to impement the export all button.
            $questPick = null;
            $button = $form->get('export');
            //, then we need to get the array of checkboxes
            if ($button->isClicked()) {
                $questPick = $request->get('questions');
            }
            $questionItems = $this->_prepExportText($questPick);
            $response = $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_doexport.html.twig', ['questions' => $questionItems]);
            return $response;
        }

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_export.html.twig', ['form' => $form->createView(), 'questions' => $questions]);
    }

    /**
     * _prepExportText - given a list of question ids, grab them and make an array of questions.
     * 
     * @param type $qIds
     */
    private function _prepExportText($qIds = null) {
        $questions = array();
        $em = $this->getDoctrine()->getManager();
        //if this is null, we want all the questions
        if ($qIds == null) {
            //get them all
            $qb = $em->createQueryBuilder();
            // add select and from params
            $qb->select('u')
                    ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
            $query = $qb->getQuery();
            // execute query
            $questions = $query->getResult();
        } else {
            foreach ($qIds as $id) {
                $questions[] = $em->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $id);
            }
        }
        return $questions;
    }

    /**
     * @Route("/upgradeoldquestions")
     * 
     * export the chosen questions. First step. This displays the interface to export the questions
     * @return Response
     * @throws AccessDeniedException
     */
    public function upgradeoldquestionsAction() {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        //walk through all the questions and get rid of serialized data.
        //get the questions
        $em = $this->getDoctrine()->getManager();
        //get them all
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
        $query = $qb->getQuery();
        // execute query
        $questions = $query->getResult();
        foreach ($questions as $question) {
            $type = $question->getQuickcheckqType();
            if (($type == AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE) ||
                    ($type == AdminController::_QUICKCHECK_MULTIANSWER_TYPE) ||
                    ($type == AdminController::_QUICKCHECK_MATCHING_TYPE)) {
                try {
                    $potAnswer = $question->getQuickcheckqAnswer();
                    $potParam = $question->getQuickcheckqParam();
                    //if param is empty, then we don't need to do anything.
                    if ($potParam == '')
                        continue;
                    $answers = unserialize($potAnswer);
                    $params = unserialize($potParam);
                } catch (\Exception $e) {
                    continue;
                }
                $newAnswer = '';
                $array_size = count($answers);
                for ($i = 0; $i < $array_size; $i++) {
                    $newAnswer .= "$answers[$i]|$params[$i]\n";
                }
                $question->setQuickcheckqParam('');
                $question->setQuickcheckqAnswer($newAnswer);
                $em->merge($question);
            }
            if ($type == AdminController::_QUICKCHECK_TF_TYPE) {
                $answer = $question->getQuickcheckqAnswer();
                if ($answer === '0') {
                    $question->setQuickcheckqAnswer('no');
                } else if ($answer === '1') {
                    $question->setQuickcheckqAnswer('yes');
                }
            }
        }
        $em->flush();
        $this->addFlash('status', $this->__("Questions updated."));
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
        return $response;
    }

    /**
     * @Route("/findmyid")
     *
     * Match the ID of a question using the first 250 charaters of the stem. This is useful for students trying to look up the QID,
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function findmyidAction(){
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        //get them all
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
            ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u')
            ->where('u.id > ?1' )
            ->setParameter(1, '1300');
        $query = $qb->getQuery();
        // execute query
        $questions = $query->getResult();

        return $this->render('PaustianQuickcheckModule:Admin:quickcheck_admin_findmyid.html.twig', ['questions' => $questions]);
    }

    /**
     * @Route("/cleanCatDupes")
     *
     * export the chosen questions. First step. This displays the interface to export the questions
     * @return Response
     * @throws AccessDeniedException
     */

    public function cleanCatDupesAction(){
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        //get all the questions
        $em = $this->getDoctrine()->getManager();
        // create a QueryBuilder instance
        $qb = $em->createQueryBuilder();

        // add select and from params
        $qb->select('u')
            ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        // execute query
        $questions = $query->getResult();
        $dups = 0;
        foreach($questions as $question){
            $qid = $question->getId();
            //find it in the category table
            $qb2 = $em->createQueryBuilder();
            $qb2->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionCategory', 'u')
                ->where('u.entity=:ent' )
                ->setParameter('ent', $qid);
            $query2 = $qb2->getQuery();
            $result = $query2->getResult();
            if(count($result) > 1){
                //delete the second category
                $em->remove($result[1]);
                $dups++;
            }
        }
        $em->flush();
        $this->addFlash('status', $dups  . $this->__(" questions removed."));
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
        return $response;
    }
}
