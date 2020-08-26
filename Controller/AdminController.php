<?php

declare(strict_types=1);

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
use Doctrine\Persistence\ObjectManager;
//use mysql_xdevapi\Exception as \Exception;
use Paustian\QuickcheckModule\API\UUID;
use Paustian\QuickcheckModule\Form\ExamineAllForm;
use Paustian\QuickcheckModule\PaustianQuickcheckModule;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Symfony\Component\Routing\RouterInterface;
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
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;

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

    const _STATUS_VIEWABLE = 0;
    const _STATUS_MODERATE = 1;
    const _STATUS_FOREXAM = 2;


    /**
     * @Route("")
     * @Theme("admin")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) : Response {

        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        // Return a page of menu items.
        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_menu.html.twig');
    }

    /**
     * @Route("/edit/{exam}")
     * 
     * * form to add new exam  
     *
     * Create a new exam
     * @Theme("admin")
     * @param Request $request
     * @param QuickcheckExamEntity|null $exam
     * @return mixed The form for creating a new exam response object
     */
    public function editAction(Request $request, QuickcheckExamEntity $exam = null) : Response {
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

        if($questions == ""){
            $this->addFlash('error', $this->trans('You need to create questions before you can create an exam.'));
            return $this->_determineRedirect($request,'paustianquickcheckmodule_admin_editmcquest');
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $questPick = $request->get('questions');
            $exam->setQuickcheckquestions($questPick);
            if (!$doMerge) {
                $exam->setQuickcheckrefid(0);
                $em->persist($exam);
            }
            $em->flush();
            $this->addFlash('status', $this->trans('Exam saved.'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_exam.html.twig', array(
                    'form' => $form->createView(), 'questions' => $questions
        ));
    }

    /**
     * @Route ("/delete/{exam}")
     * @Theme("admin")
     *  deleteAction - delete the exam.
     * @param Request $request
     * @param QuickcheckExamEntity|null $exam
     * @return Response
     */
    public function deleteAction(Request $request, QuickcheckExamEntity $exam = null) : Response {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_modify'));
        if ($exam == null) {
            //you want the edit interface, which has a delete option.
            return $response;
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($exam);
        $em->flush();
        $this->addFlash('status', $this->trans('Exam Deleted.'));
        return $response;
    }

    /**
     * @Route ("/deletequestion/{question}", options={"expose"=true})
     * @Theme("admin")
     * deleteQuestionAction - delete the question.
     *
     * @param Request $request
     * @param QuickcheckQuestionEntity|null $question
     * @return Response
     */
    public function deleteQuestionAction(Request $request, QuickcheckQuestionEntity $question = null) : Response {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $response = $this->_determineRedirect($request, 'paustianquickcheckmodule_admin_editquestions');
            //$this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
        $json = null;
        if ($question == null) {
            $id = $request->request->get('id');
            if(!isset($id)){
                //you want the edit interface, which has a delete option.
                return $response;
            }
            $json = true;
            $question = $em->getRepository('PaustianQuickcheckModule:QuickcheckQuestionEntity')->findOneBy(['id' => $id]);
        }
        $id = $question->getId();
        $this->_removeQuestionFromExams($em, $id);
        $em->remove($question);
        $em->flush();
        $this->addFlash('status', $this->trans('Question Deleted.'));
        if($json){
            $jsonReply = [
                'id' => $id,
                'success' => true
            ];
            return  new JsonResponse($jsonReply);
        }
        return $response;
    }

    /**
     * remove a deleted question from an exam
     * @param ObjectManager $em
     * @param int $id
     * @return bool
     */

    private function _removeQuestionFromExams(ObjectManager $em, int $id) : bool {
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
     * @Theme("admin")
     * modify an exam
     *
     * Set up a form to present all the exams and let the user choose
     * The one to modify
     *
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    public function modifyAction(Request $request) : Response{
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $exams = $this->getDoctrine()->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity')->get_all_exams();

        if (!$exams) {
            $this->addFlash('error', $this->trans('There are no exams to edit'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_modify.html.twig', ['exams' => $exams]);
    }

    /**
     * @Route ("/attach/", methods={"POST"})
     * @Theme("admin")
     * @param Request $request
     * @throws AccessDeniedException
     * @return RedirectResponse
     *
     * Parameters of the request are:
     *  ret_url the URL to return to after being done
     *  art_id the article Id of the item that the exam is being attached to
     *  exam the exam that is being attached to the article
     *
     */
    Stopped here. I need to finish up the javascript and make this a Json event. There are examples in the book module on how to do this.
    public function attachAction(Request $request) : RedirectResponse {
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
        }
        if (isset($attach)) {
            if (!isset($art_id) || !isset($exam)) {
                throw new NotFoundHttpException($this->trans('An article id or exam id are missing'));
            }
            //modify the exam by grabbing it and then changing or adding the art_id
            $new_exam = $em->find('PaustianQuickcheckModule:QuickcheckExamEntity', $exam);
            if (null === $new_exam) {
                throw new \Doctrine\ORM\NoResultException($this->trans('An exam was not found, when it should have been.'));
            }
            $new_exam->setQuickcheckrefid((int)$art_id);
            $request->getSession()->getFlashBag()->add('status', $this->trans('The exam was attached.'));
        } else {
            $request->getSession()->getFlashBag()->add('status', $this->trans('The exam was removed.'));
        }
        $em->flush();
        //finally return to the page that called.
        return new RedirectResponse($ret_url);
    }

    /**
     * _build_question_list.
     * category it is in.
     * @param array|null $ckList
     * @return array Listing the text, id and type of each question and the
     */
    private function _build_questions_list(array $ckList = null) : array {
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
            return [];
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
                $questions[$this->trans('Uncategorized')][] = $built_question;
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
     * @param QuickcheckQuestionEntity $question
     * @param bool $doMerge
     * @param string $flashText
     * @param Response $redirect
     * @return Response
     */
    private function _persistQuestion(QuickcheckQuestionEntity $question, bool $doMerge, string $flashText, Response $redirect) :Response {
        $em = $this->getDoctrine()->getManager();
        if (!$doMerge) {
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
    private function _persistQuestionList(array $questionList, $categories) : void {
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
        }
        $em->flush();
    }

    /**
     * @param Request $request
     * @param $path
     * @return Response
     */
    private function _determineRedirect(Request $request, string $path) : Response{
        $fromModifyForm = $request->query->get('modify');
        if(!isset($fromModifyForm)){
            $fromModifyForm = 0;
        }
        $response = null;
        switch($fromModifyForm) {
                case 0:
                    $response = $this->redirect($this->generateUrl($path));
                    break;
                case 1:
                    $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_editquestions'));
                    break;
                case 2:
                    $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_examinemoderated'));
            }
        return $response;
    }

    /**
     * @Route("/setquestion", options={"expose"=true}, methods={"POST"})
     * @Theme("admin")
     * @param Request $request
     * @return JsonResponse
     */
    public function setQuestionAction(Request $request) :JsonResponse{
        $id = $request->request->get('id');
        $text = $request->request->get('qText');
        $answer = $request->request->get('qAnswer');
        $explanation = $request->request->get('qExpan');
        $status = (int) $request->request->get('qStatus');
        $canSave = isset($id);
        if($canSave){
            $em = $this->getDoctrine()->getManager();
            $question = $em->getRepository('PaustianQuickcheckModule:QuickcheckQuestionEntity')->findOneBy(['id' => $id]);
            $question->setQuickcheckqText($text);
            $question->setQuickcheckqAnswer($answer);
            $question->setQuickcheckqExpan($explanation);
            $question->setStatus($status);
            $em->flush();
        }
        $jsonReply = ['id' => $id,
            'cansave' => $canSave,
            'qText' => $text,
            'qAnswer' => $answer,
            'qExpan' => $explanation,
            'qStatus' => $status];

        return  new JsonResponse($jsonReply);
    }

    /**
     * @Route("/javaedit", options={"expose"=true}, methods={"POST"})
     * @Theme("admin")
     * @param Request $request
     * @return JsonResponse
     */
    public function javaEditAction(Request $request) : JsonResponse{
        $id = $request->request->get('id');
        //dummy values if id is not set.
        $jsonReply = ['id' => '0',
                    'qText' => '',
                    'qAnswer' => '',
                    'qExpan' => ''];
        if(isset($id)){
            $em = $this->getDoctrine()->getManager();
            $question = $em->getRepository('PaustianQuickcheckModule:QuickcheckQuestionEntity')->findOneBy(['id' => $id]);
            $jsonReply = [
                'id' => $question->getId(),
                'qText' => $question->getQuickcheckqText(),
                'qAnswer' => $question->getQuickcheckqAnswer(),
                'qExpan' => $question->getQuickcheckqExpan()
            ];
        }

        return  new JsonResponse($jsonReply);
    }

    /**
     * @Route("/edittextquest/{question}")
     * @Theme("admin")
     * form to add new text question
     *
     * Create a new quick_check question
     *
     * @param Request $request
     * @param QuickcheckQuestionEntity $question
     * @return Response
     */

    public function editTextQuestAction(Request $request,
                                        QuickcheckQuestionEntity $question = null,
                                        HookDispatcherInterface $hookDispatcher) :Response{
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
            $question->setStatus(self::_STATUS_MODERATE);
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(TextQuestion::class, $question);
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('quickcheck.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('delete')->isClicked()){
                return $this->deleteQuestionAction($request, $question);
            }
            $response = $this->_determineRedirect($request, 'paustianquickcheckmodule_admin_edittextquest');
            return $this->_persistQuestion($question, $doMerge, $this->trans('Text question saved!'), $response);
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_new_text_question.html.twig', array(
            'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ));
    }

    /**
     * 
     * @Route("/editmatchquest/{question}")
     * @Theme("admin")
     * form to add new matching question
     *
     * Create a new quick_check question
     * @param Request $request
     * @param QuickcheckQuestionEntity|null $question
     * @return Response
     */
    public function editMatchQuestAction(Request $request,
                                         QuickcheckQuestionEntity $question = null,
                                         HookDispatcherInterface $hookDispatcher) :Response {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
            $question->setStatus(self::_STATUS_MODERATE);
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(MatchQuestion::class, $question);
        //Add form hooks. For example Scribyte
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('quickcheck.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('delete')->isClicked()){
                return $this->deleteQuestionAction($request, $question);
            }
            $response = $this->_determineRedirect($request, 'paustianquickcheckmodule_admin_editmatchquest');
            return $this->_persistQuestion($question, $doMerge, $this->trans('Matching question saved!'), $response);
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_new_match_question.html.twig', array(
                    'form' => $form->createView(),
                    'hook_templates' => $formHook->getTemplates()
        ));
    }

    /**
     * @Route("/edittfquest/{question}")
     * @Theme("admin")
     * form to add new TF question
     *
     * Create a new quick_check question
     * @param Request $request
     * @param QuickcheckQuestionEntity $question
     * @return Response
     */
    public function editTFQuestAction(Request $request,
                                      QuickcheckQuestionEntity $question = null,
                                      HookDispatcherInterface $hookDispatcher) : Response {

        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
            $question->setStatus(self::_STATUS_MODERATE);
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(TFQuestion::class, $question);
        //Add form hooks. For example Scribyte
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('quickcheck.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        /** @var \Doctrine\ORM\EntityManager $em */
        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('delete')->isClicked()){
                return $this->deleteQuestionAction($request, $question);
            }
            $response = $this->_determineRedirect($request,'paustianquickcheckmodule_admin_edittfquest');
            return $this->_persistQuestion($question, $doMerge, $this->trans('True/False question saved!'), $response);
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_new_tf_question.html.twig', array(
                    'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ));
    }

    /**
     * @Route("/editmcquest/{question}")
     * @Theme("admin")
     * Form to add a new multiple choice question
     * @param Request $request
     * @param QuickcheckQuestionEntity|null $question
     * @return Response
     *
     * Test data
     * HEre is an answer|100
     * HEre is another|0
     */
    public function editMCQuestAction(Request $request,
                                      QuickcheckQuestionEntity $question = null,
                                      HookDispatcherInterface $hookDispatcher) : Response{
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
            $question->setStatus(self::_STATUS_MODERATE);
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(MCQuestion::class, $question);
        //Add form hooks. For example Scribyte
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('quickcheck.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('delete')->isClicked()){
                return $this->deleteQuestionAction($request, $question);
            }
            $response = $this->_determineRedirect($request,'paustianquickcheckmodule_admin_editmcquest');
            return $this->_persistQuestion($question, $doMerge, $this->trans('Multiple-Choice  question saved!'), $response);
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_new_mc_question.html.twig', array(
                    'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ));
    }

    /**
     * @Route ("/editmansquest/{question}")
     * @Theme("admin")
     * Edit a multiple answer question.
     *
     * @param Request $request
     * @param QuickcheckQuestionEntity|null $question
     * @return Response
     */
    public function editMANSQuestAction(Request $request,
                                        QuickcheckQuestionEntity $question = null,
                                        HookDispatcherInterface $hookDispatcher) : Response {

        if (!$this->hasPermission($this->name . '::', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $doMerge = false;
        if (null === $question) {
            $question = new QuickcheckQuestionEntity();
            $question->setStatus(self::_STATUS_MODERATE);
        } else {
            $doMerge = true;
        }
        //I need to add the use declaration for this class. 
        $form = $this->createForm(MAnsQuestion::class, $question);
        //Add form hooks. For example Scribyte
        $formHook = new FormAwareHook($form);
        $hookDispatcher->dispatch('quickcheck.form_aware_hook.article.edit', $formHook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('delete')->isClicked()){
                return $this->deleteQuestionAction($request, $question);
            }
            $response = $this->_determineRedirect($request,'paustianquickcheckmodule_admin_editmansquest');
            return $this->_persistQuestion($question, $doMerge, $this->trans('Multiple-Answer question saved!'), $response);
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_new_mans_question.html.twig', array(
                    'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ));
    }

    /**
     * @Route ("/modifyquestion/{question}")
     * @Theme("admin")
     * modifyquestion
     * 
     * Take the form data from editquestionsAction and dispatch it to the right question interface.
     *
     * @throws AccessDeniedException
     * @param Request $request
     * @param QuickcheckQuestionEntity|null $question
     * @return Response
     *
     */
    public function modifyquestionAction(Request $request, QuickcheckQuestionEntity $question = null) : Response {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if(null === $question){
            $id = $request->request->get('questions', null);
            $redirect_url = $this->get('router')->generate('paustianquickcheckmodule_admin_editquestions', array(), RouterInterface::ABSOLUTE_URL);
            if (!isset($id) || !is_numeric($id)) {
                $request->getSession()->getFlashBag()->add('status', $this->trans("You need to pick a question"));
                return new RedirectResponse($redirect_url);
            }
            $question = $this->getDoctrine()->getManager()->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $id);
            if (!$question) {
                $request->getSession()->getFlashBag()->add('status', $this->trans("A question with that id does not exist"));
                return new RedirectResponse($redirect_url);
            }
        }
        $id = $question->getId();

        $questionType = $question->getQuickcheckqType();
        $button = $request->request->get('edit');
        $modify = 1;
        if (!isset($button)) {
            $button = $request->request->get('delete', null);
            if(null === $button){
                //if there is no edit or delete button the call is coming from the moderation interface (examinemoderatedAction)
                //thus, we want a different return response.
                $modify = 2;
            }
        }
        $response = null;
        if (($button == 'edit') || (null === $button)) {
            switch ($questionType) {
                case self::_QUICKCHECK_TEXT_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_edittextquest', array('question' => $id, 'modify' => $modify)));
                    break;
                case self::_QUICKCHECK_MATCHING_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_editmatchquest', array('question' => $id, 'modify' => $modify)));
                    ;
                    break;
                case self::_QUICKCHECK_MULTIANSWER_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_editmansquest', array('question' => $id, 'modify' => $modify)));
                    ;
                    break;
                case self::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_editmcquest', array('question' => $id, 'modify' => $modify)));
                    ;
                    break;
                case self::_QUICKCHECK_TF_TYPE:
                    $response = new RedirectResponse($this->generateUrl('paustianquickcheckmodule_admin_edittfquest', array('question' => $id, 'modify' => $modify)));
                    ;
                    break;
            }
        } else if ($button == 'delete') {
            return $this->deleteQuestionAction($request, $question);
        }
        return $response;
    }

    /**
     * @Route("/editquestions")
     * @Theme("admin")
     * edit questions
     *
     *  This is the interface for modifying and deleting questions. I combined the
     *  two to make it more accessible to the user. The function displays a list of
     *  questions, and from there a user can edit or delete a question.
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     * 
     */

    public function editquestionsAction(Request $request) :Response{
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $questions = $this->_prep_question_list('radio');

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_editquestions.html.twig', array('questions' => $questions));
    }

    /**
     * _prep_question_list
     * 
     * returns either just a list of question names, or a
     * form array of questions. From there you can render how you please in a response
     * I may just want it to return the array (could just get rid of this funciton
     * and then add it as part of a form. I will have to see.
     *
     * @param string $buttons
     * @param array $ckquestions
     * @return string
     */
    private function _prep_question_list(string $buttons = 'radio', array $ckquestions = null) : string {

        $questions = $this->_build_questions_list($ckquestions);

        if (!$questions) {
            $this->addFlash('error', $this->trans('There are no questions to modify. Create some first.'));

            return "";
        }
        ksort($questions);
        $html = $this->renderView('@PaustianQuickcheckModule/Admin/quickcheck_admin_qpart.html.twig', [ 'questions' => $questions,
            'buttons' => $buttons]);


        return $html;
    }

    /**
     * @Route("/categorize")
     * @Theme("admin")
     * Present an interface for putting uncategorized quesitons into categories
     * 
     * @param Request $request
     * @return Response
     */
    public function categorizeAction(Request $request) : Response {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $questions = $this->_prep_question_list('checkbox');

        $form = $this->createForm(CategorizeForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questPick = $request->get('questions');

            $categories = $form->get('categories')->getData();
            $this->_persistQuestionList($questPick, $categories);
            $this->addFlash('status', $this->trans('Questions recategorized.'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_categorize'));
            return $response;
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_categorize.html.twig', ['form' => $form->createView(), 'questions' => $questions]);
    }


    /**
     * @Route("/findunanswered")
     * @Theme("admin")
     * findunanswered
     *
     * This is a quick function to find all the unexplained questions in the module
     * It's really a hack and isn't something you should be calling, I am just using
     * it for clean up of some previous data. It will likely go away in a future release
     *
     *  @return Response
     *  @throws AccessDeniedException
     */
    public function findunansweredAction() : Response {
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
            $this->addFlash('error', $this->trans("There are no unexplained questions."));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_findunanswered.html.twig', ['count' => count($questions), 'questions' => $questions]);
    }

    /**
     * 
     * @param string $xmlQuestionText - the xml text to parse
     * @param type $category - the categories to assign it.
     * 
     */
    private function _parseImportedQuizXML(string $xmlQuestionText, ArrayCollection $category) {
        //An awesome function for parsing simple xml.
        //First we need to scrumb out our xml tags before doing this magic
        preg_match_all("|<question>(.*?)</question>|s", $xmlQuestionText, $questionArray);

//grab the manager for saving the data.
        $em = $this->getDoctrine()->getManager();
        foreach ($questionArray[1] as $q_item) {
            $doMerge = false;
            if(preg_match("|<qid>([0-9]{1,3})</qid>|", $q_item, $qId)){
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
                    $question->setId((int)$id);
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
                    throw new NotFoundHttpException($this->trans('Unrecognized question type, was your qtype empty in the xml file?'));
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
            if (!$doMerge) {
                $em->persist($question);
            }
        }
        $em->flush();
    }

    /**
     * @Route("/importquiz")
     * @Theme("admin")
     * set up the interface to import an xml file of quiz questions.
     * @return Response
     * @throws AccessDeniedException
     */
    public function importquizAction(Request $request) :Response {
        //you have to have edit access to do this
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ImportText::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $xmlQuestionText = $form->get('importText')->getData();
            $category = $form->get('categories')->getData();
            //take the xml that is imported, and parse it into an array
            //That array should have filled out a new question entity which it shoudl return
            $questions = $this->_parseImportedQuizXML($xmlQuestionText, $category);
            $this->addFlash('status', $this->trans("Questions imported."));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_importquiz'));
            return $response;
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_import.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/exportquiz")
     * @Theme("admin")
     * export the chosen questions. First step. This displays the interface to export the questions
     * @return Response
     * @throws AccessDeniedException
     */
    public function exportquizAction(Request $request) :Response {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $questions = $this->_prep_question_list('checkbox');

        $form = $this->createForm(ExportForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //I need to impement the export all button.
            $questPick = null;
            $button = $form->get('export');
            //, then we need to get the array of checkboxes
            if ($button->isClicked()) {
                $questPick = $request->get('questions');
            }
            $questionItems = $this->_prepExportText($questPick);
            $response = $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_doexport.html.twig', ['questions' => $questionItems]);
            return $response;
        }

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_export.html.twig', ['form' => $form->createView(), 'questions' => $questions]);
    }

    /**
     * _prepExportText - given a list of question ids, grab them and make an array of questions.
     *
     * @param array|null $qIds
     * @return array
     */
    private function _prepExportText(array $qIds = null) :array {
        $questions = array();
        $em = $this->getDoctrine()->getManager();
        //if this is null, we want all the questions
        if (null === $qIds) {
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
     * @Theme("admin")
     * export the chosen questions. First step. This displays the interface to export the questions
     * @return Response
     * @throws AccessDeniedException
     */
    public function upgradeoldquestionsAction() : Response {
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
        $this->addFlash('status', $this->trans("Questions updated."));
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
        return $response;
    }

    /**
     * @Route("/findmyid")
     * @Theme("admin")
     * Match the ID of a question using the first 250 charaters of the stem. This is useful for students trying to look up the QID,
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function findmyidAction() : Response {
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

        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_findmyid.html.twig', ['questions' => $questions]);
    }

    /**
     * @Route("/cleanCatDupes")
     * @Theme("admin")
     * export the chosen questions. First step. This displays the interface to export the questions
     * @return Response
     * @throws AccessDeniedException
     */

    public function cleanCatDupesAction() : Response {
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
        $this->addFlash('status', $dups  . $this->trans(" questions removed."));
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
        return $response;
    }

    /**
     * Add the category name to each question.
     * @param array $questions
     * @param string $category
     * @return array
     */

    private function _categorizeQuestions(array $questions, string $category='all') : array {
        $returnQuestions = [];
        foreach($questions as $question){
            $cat = $question->getCategories()->first();
            $name = "Uncategorized";
            if($cat !== false){
                $name = $cat->getCategory()->getName();
            }
            if($category === 'all'){
                $returnQuestions[$question->getId()] = $name;
            } else {
                if($name === $category){
                    $returnQuestions[$question->getId()] = $name;
                }
            }

        }
        return $returnQuestions;
    }

    /**
     * @Route("/examinemoderated")
     * @Theme("admin")
     * @return Response
     * @throws AccessDeniedException
     */

    public function examinemoderatedAction(Request $request) : Response {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        //get them all
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
            ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u')
            ->where('u.status = ?1' )
            ->setParameter(1, '1');
        $query = $qb->getQuery();
        // execute query
        $questions = $query->getResult();
        $qCategories = $this->_categorizeQuestions($questions);

        return $this->render("@PaustianQuickcheckModule/Admin/quickcheck_admin_examinequestions.html.twig",
            ['questions' => $questions,
             'categories' => $qCategories,
                'deleteRows' => true]);
    }

    /**
     * @Route("/examineall")
     * @Theme("admin")
     * @return Response
     * @throws AccessDeniedException
     */

    public function examineallAction(Request $request) :Response {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ExamineAllForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $searchText = explode(' ', $formData['searchtext']);
            $catCollection = $formData['categories'];
            $category = "";
            if(!$catCollection->isEmpty()){
                $category = $catCollection->first();
            }
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository("PaustianQuickcheckModule:QuickcheckQuestionEntity");
            $questions = [];
            if($searchText){
                $questions = $repo->getSearchResults($searchText, 'AND', true);
            } else {
                //get them all
                $qb = $em->createQueryBuilder();
                // add select and from params
                $qb->select('u')
                    ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
                $query = $qb->getQuery();
                // execute query
                $questions = $query->getResult();
            }

            //filter out only one category if $category != ""
            if($category !== ""){
                $catName = $category->getCategory()->getName();
                $questions = array_filter($questions, function($element) use ($catName){
                    $cat = $element->getCategories()->first();
                    $name = $cat->getCategory()->getName();
                    return ($catName === $name);
                });
            }
            $qCategories = $this->_categorizeQuestions($questions);

            return $this->render("@PaustianQuickcheckModule/Admin/quickcheck_admin_examinequestions.html.twig",
                ['questions' => $questions,
                    'categories' => $qCategories,
                    'deleteRows' => false]);
        }
        return $this->render("@PaustianQuickcheckModule/Admin/quickcheck_admin_searchallquestions.html.twig",
                ['form' => $form->createView()]);
    }

    /**
     * _findHidden
     * @return array
     */
    public function _findHidden() : array {
        $em = $this->getDoctrine()->getManager();
        //get them all
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
            ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u')
            ->where('u.status = ?1' )
            ->setParameter(1, '2');
        $query = $qb->getQuery();
        // execute query
        return $query->getResult();
    }

    /**
     * @Route("/hiddentopublic")
     * @Theme("admin")
     * @param Request $request
     * @return RedirectResponse
     * @throws AccessDeniedException
     */

    public function hiddentopublicAction(Request $request) : RedirectResponse{
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $questions = $this->_findHidden();
        $em = $this->getDoctrine()->getManager();
        foreach ($questions as $question){
            $question->setStatus(AdminController::_STATUS_VIEWABLE);
            $em->merge($question);
        }
        $em->flush();
        $this->addFlash('status', $this->trans('Hidden questions added to public database.'));
        return $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
    }

    /**
     * @Route("createexamfromhidden")
     * @Theme("admin")
     * @return RedirectResponse
     * @throws AccessDeniedException
     * @param Request $request
     *
     */
    public function createexamfromhiddenAction(Request $request) : RedirectResponse{
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        //get all the hidden question, create an exam.
        $questions = $this->_findHidden();
        $questionIds = [];

        foreach($questions as $question){
            $questionIds[] = $question->getId();
        }
        $newExam = new QuickcheckExamEntity();
        $newExam->setQuickcheckquestions($questionIds);
        $newExam->setQuickcheckname($this->trans("New Exam from Hidden Questions"));
        $newExam->setQuickcheckrefid(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($newExam);
        $em->flush();
        //now redirect to the new exam
        $id = $newExam->getId();
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_edit', ['exam' => $id]));
        return $response;
    }

    /**
     * @Route("/examinehidden")
     * @Theme("admin")
     * @return Response
     * @throws AccessDeniedException
     */

    public function examinehiddenAction(Request $request) : Response{
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $questions = $this->_findHidden();
        $qCategories = $this->_categorizeQuestions($questions);

        return $this->render("@PaustianQuickcheckModule/Admin/quickcheck_admin_examinequestions.html.twig",
            ['questions' => $questions,
                'categories' => $qCategories,
                'deleteRows' => true]);
    }
}
