<?php

/**
 * quickcheck Module
 *
 * The quickcheck module is a module for entering microbial strain data into
 * a mysql database. The completed database can then be used to identify unknown
 * microbes. I also used this module as an example Zikula module to demonstrates
 * some of the frameworks functionality
 *
 * Purpose of file:  Table information for quickcheck module --
 *                   This file contains all information on database
 *                   tables for the module
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      2.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009-2010 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule\Controller;

use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Zikula\Core\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use CategoryUtil;
use DataUtil;
use Paustian\QuickcheckModule\Controller\AdminController;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController {

    /**
     * @Route("")
     * 
     * view
     * This routine allows for the user to perform the only function, creating a
     * quiz.
     * Using all (or a subset of) the questions available, create a multiple choice
     * quiz.
     *
     * Params: none
     * Returns: the quiz. This can be graded by the gradequiz funciton below
     */
    public function indexAction() {
        //securtiy check first
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $categoryData = $this->_getCategories();
        $counts = [];
        foreach($categoryData as $categoryItem){
            $counts[$categoryItem['id']] = $this->_countItems($categoryItem['id']);
        }

        //$categoryData = $propertiesdata[0]['subcategories'];

        return $this->render('PaustianQuickcheckModule:User:quickcheck_user_index.html.twig',
                ['categories' => $categoryData,
                    'counts' => $counts]);
    }

    /**
     * Get the categories registered for the Pages
     *
     * @return array
     */
    private function _getCategories() {
        $em = $this->getDoctrine()->getManager();
        $registryRepository = $em->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity');
        $categoryRegistries = $registryRepository->findBy(['modname' => 'PaustianQuickcheckModule']);
        $baseCategory = $categoryRegistries[0]->getCategory();
        $children = $baseCategory->getChildren();
        return $children->toArray();
    }

    /**
     * utility function to count the number of items held by this module
     *
     * @param array $args Arguments.
     *
     * @return integer number of items held by this module
     */
    private function _countItems($category) {

        $em = $this->getDoctrine()->getManager();

        if (isset($category) && !empty($category)) {
            $qb = $em->createQueryBuilder();

            $qb->select('count(p)')
                    ->from('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', 'p')
                    ->join('p.categories', 'c')
                    ->where('c.category = :categories')
                    ->setParameter('categories', $category);

            return $qb->getQuery()->getSingleScalarResult();
        }
        $qb = $em->createQueryBuilder();
        $qb->select('count(p)')->from('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @Route("/createExam")
     * @Method("POST")
     * 
     * @param $request
     * @return Response
     */
    public function createExamAction(Request $request) {
        //you have to have edit access to do this
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $ret_url = $this->get('router')->generate('paustianquickcheckmodule_user_index', array(), RouterInterface::ABSOLUTE_URL);

        $num_quests = $request->request->get('num_questions', null);
        //now create the quiz

        $em = $this->getDoctrine()->getManager();
        // create a QueryBuilder instance
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        $questions = $query->getResult();
        //bin the questions into separate categories
        $bin_questions = $this->_binQuestionCategories($questions);
        $quiz_questions = array(); //the array that will hold the questions
        $random_questions = array(); //the random questions from a category
        $examRepo = $this->getDoctrine()->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');

        foreach ($num_quests as $catid => $number_of_questions) {
            if( (!is_numeric($number_of_questions)) || ($number_of_questions <= 0) ){
                continue;
            }
            if ($number_of_questions > 0) {
                //grab the random keys from the array of questions
                $random_questions = array_rand($bin_questions[$catid], $number_of_questions);
                if ($number_of_questions == 1) {
                    $the_question = $examRepo->unpackQuestion($bin_questions[$catid][$random_questions]);
                    $quiz_questions[] = $the_question;
                } else {
                    //now fill our array with these questions
                    foreach ($random_questions as $qIndex) {
                        $the_question = $examRepo->unpackQuestion($bin_questions[$catid][$qIndex]);
                        $quiz_questions[] = $the_question;
                    }
                }
            }
        }
        if (count($quiz_questions) == 0) {
            $request->getSession()->getFlashBag()->add('error', $this->__('You need to pick the number of questions.'));
            return new RedirectResponse($ret_url);
        }
        //shuffle the array to randomize the order in which they get asked.
        shuffle($quiz_questions);
        //build the sq_id array. This is used to grade the quesitons
        $sq_ids = array();
        foreach ($quiz_questions as $question) {
            $sq_ids[] = $question->getId();
        }
        //I need to change this so that it sends back it's own response. What this entails is just getting the data that it
        //needs and then sending it back.
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return new Response($this->render('PaustianQuickcheckModule:User:quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                    'q_ids' => \serialize($sq_ids),
                    'questions' => $quiz_questions,
                    'return_url' => $ret_url,
                    'print' => false,
                    'exam_name' => $this->__('Practice Exam')]);
    }

    /**
     * _binQuestionCategories - Given an array of questions bin them into categories based upon their category id.
     * 
     * @param type $questions
     * @return array
     */
    private function _binQuestionCategories($questions) {
        $binned_questions = array();
        foreach ($questions as $question) {
            $perCollect = $question->getCategories();
            $aCollection = $perCollect->unwrap();
            $category = $aCollection->current();
            if ($category !== false) {
                $reg_id = $category->getCategory()->getId();
                $binned_questions[$reg_id][] = $question;
            }
        }
        return $binned_questions;
    }

    private function _fetch_cat_questions($in_questions, $in_cat_id) {
        $ret_questions = array();
        $started = false;
        foreach ($in_questions as $question) {
            if ($question['__CATEGORIES__']['Main']['id'] == $in_cat_id) {
                $started = true;
                $ret_questions[] = $question;
            } else if ($started) {
                //we can break out of the loop now because we have collected all
                //the questions (they are sorted)
                break;
            }
        }
        return $ret_questions;
    }

    /**
     * @Route("/display/{exam}")
     * 
     * This displays an quiz from the database, or it displays a quiz set up by 
     * the student for self study.
     * 
     * Date: November 3 2015
     * @author Timothy Paustian
     * 
     * @param Request the exam info that holds the questions* 
     * @return Response
     *
     */
    public function displayAction(Request $request, QuickcheckExamEntity $exam = null, $return_url = "", $print = false) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw AccessDeniedException();
        }

        $examQuestions = array();
        $examName = "";
        if ($exam !== null) {
            $examQuestions = $exam->getQuickcheckquestions();
            $examName = $exam->getQuickcheckname();
        } else {
            $examData = $request->request->get('exam', null);
            if (!isset($examData)) {
                return null;
            }
            $examQuestions = $examData['questions'];
            $examName = $exam['name'];
            $return_url = $request->request->get('ret_url');
        }
        $sq_ids = array();
        $letters = array();
        $questions = array();
        $repo = $this->getDoctrine()->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $repo->render_quiz($examQuestions, $questions, $sq_ids, $letters);

        return $this->render('PaustianQuickcheckModule:User:quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                    'q_ids' => $sq_ids,
                    'questions' => $questions,
                    'return_url' => $return_url,
                    'exam_name' => $examName,
                    'admininterface' => '',
                    'print' => $print]);
    }

     /**
     * @Route("/print/{exam}")
     * 
     * This displays an quiz from the database, or it displays a quiz set up by 
     * the student for self study.
     * 
     * Date: November 3 2015
     * @author Timothy Paustian
     * 
     * @param Request the exam info that holds the questions* 
     * @return Response
     *
     */
    public function printAction(Request $request, QuickcheckExamEntity $exam = null) {
        return $this->displayAction($request, $exam, "", true);
    }
    /**
     * @Route("/gradeexam")
     * @Method("POST")
     * 
     * gradequizAction
     *
     * Here we get the information back from the quiz. We take this, extract the question ids first
     * and then find the right answer to each question. Each question answer comes back as an array, corresponding to
     * the question id. We can then compare this to the correct answer for each type.
     * @param $request
     */
    public function gradeexamAction(Request $request) {

        if (!$this->hasPermission($this->name . '::', '::', ACCESS_OVERVIEW)) {
            throw AccessDeniedException();
        }
        $return_url = $request->request->get('ret_url', null);
        $sq_ids = $request->request->get('q_ids', null);
        $q_ids = unserialize($sq_ids);
        $score = 0;
        $display_questions = array();
        $student_answers = array();
        $correct_answers = array();
        $em = $this->getDoctrine()->getManager();
        $examRepo = $this->getDoctrine()->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $ur_answer = '';

        foreach ($q_ids as $q_id) {
            $student_answer = $request->request->get($q_id, null);
            $question = $em->find('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', $q_id);
            //we need to unpack the question so that we can display it.
            $examRepo->unpackQuestion($question, false);
            if (!isset($student_answer)) {
                $student_answer = "";
            }
            switch ($question['quickcheckqtype']) {
                case AdminController::_QUICKCHECK_TEXT_TYPE:
                    $score += 1;
                    $ur_answer = $student_answer;
                    //we don't grade text types
                    break;
                case AdminController::_QUICKCHECK_TF_TYPE:
                    if ($student_answer === $question->getQuickcheckqanswer()) {
                        $score += 1;
                    }
                    $ur_answer = $student_answer;
                    break;
                case AdminController::_QUICKCHECK_MATCHING_TYPE:
                    //I set this up so that if all the matches are correct
                    //the order returned will be in order.
                    $student_order = $request->request->get('order_' . $q_id);
                    parse_str($student_order, $matches);
                    //the parse string breaks it down into each item that is in there (which we named 'item')
                    $match_answers = $matches['item'];
                    //walk the arrays comapring each value. I cannot use a php array
                    //function because position is important
                    $match_right = 0;
                    $size = count($match_answers);
                    for ($i = 0; $i < $size; $i++) {
                        if ($match_answers[$i] == $i) {
                            $match_right++;
                        }
                    }
                    $this_score = $match_right / $size;
                    if ($this_score >= 1) {
                        $this_score = 1;
                    }
                    $score += $this_score;
                    $ur_answer = $match_answers;
                    break;
                case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                    //the student answer containg the position of the values that
                    //they entered. Award points for correct answers.
                    $total = 0;
                    $ur_answer = array();
                    //fill an array the size of the answers with -1
                    $array_size = count($question->getQuickcheckqanswer());
                    $num_that_should = 0;
                    $marked_answers = array_fill(0, $array_size, -1);
                    if (is_array($student_answer)) {
                        foreach ($student_answer as $checked_item) {
                            $mc_answers = explode('_', $checked_item);
                            //you get points added if this is a correct mark
                            $total += $mc_answers[0];
                            if ($mc_answers[0] > 0) {
                                $num_that_should++;
                            }
                            //mark this position as one that was checked.
                            $marked_answers[$mc_answers[1]] = (int) $mc_answers[1];
                        }
                    }
                    $ur_answer = $marked_answers;
                    //substract
                    $deduction = (count($student_answer) - $num_that_should) * (100 / $array_size);
                    if ($deduction > 0) {
                        $total -= $deduction;
                    }
                    $score += $total / 100;
                    break;
                case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                    preg_match("/([0-9]{1,3}).*?([0-9])/s", $student_answer,$matches);
                    $score += $matches[1] / 100;
                    $ur_answer = $matches[2];
                    break;
            }
            //save the questions in an array for display.
            $display_questions[] = $question;
            $student_answers[] = $ur_answer;
            //reset these answers
            $ur_answer = '';
        }
        $percent = $score / count($q_ids) * 100;
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

        return new Response($this->render('PaustianQuickcheckModule:User:quickcheck_user_gradeexam.html.twig', [
                    'questions' => $display_questions,
                    'score' => $score,
                    'percent' => $percent,
                    'letters' => $letters,
                    'student_answers' => $student_answers])->getContent());
    }

    /**
     * @Route("/getpreviewhtml", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse|FatalResponse|ForbiddenResponse bid or Ajax error
     *
     * Grab all comments associated with this module and item ID and return them to the caller
     * The caller is a javascript, see the javascripts in Resources/public/js directory
     */

    public function getpreviewhtmlAction(Request $request){
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            return new ForbiddenResponse($this->__('Access forbidden since you cannot read questions.'));
        }
        //fetch the parameters for the question
        $questionText = $request->get('question');
        $answer = $request->get('answer');
        $type = $request->get('type');
        $question = new QuickcheckQuestionEntity();
        $question->setQuickcheckqType($type);
        $question->setQuickcheckqText($questionText);
        $question->setQuickcheckqAnswer($answer);
        //Create the type of object that we need for the renderexam template
        $repo = $this->getDoctrine()->getManager()->getRepository("Paustian\QuickcheckModule\Entity\QuickcheckExamEntity");
        $question = $repo->unpackQuestion($question);
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $response = $this->render('PaustianQuickcheckModule:User:quickcheck_user_preview.html.twig', ['letters' => $letters,
            'question' => $question]);
        $jsonReply = ['html' => $response->getContent()];

        return  new JsonResponse($jsonReply);
    }

}

?>
