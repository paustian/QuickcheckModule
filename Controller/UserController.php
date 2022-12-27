<?php

declare(strict_types=1);

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

use Doctrine\Persistence\ManagerRegistry;
use DoctrineProxy\__CG__\Paustian\QuickcheckModule\Entity\QuickcheckGradesEntity;
use http\Params;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

class UserController extends AbstractController {
    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        ManagerRegistry $managerRegistry
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->managerRegistry = $managerRegistry;
        $this->entityManager = $managerRegistry->getManager();
    }

    /**
     * @Route("")
     * 
     * view
     * This routine allows for the user to perform the only function, creating a
     * quiz.
     * Using all (or a subset of) the questions available, create a multiple choice
     * quiz.
     *
     * @return Response
     */
    public function index() : Response {
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

        return $this->render('@PaustianQuickcheckModule/User/quickcheck_user_index.html.twig',
                ['categories' => $categoryData,
                    'counts' => $counts]);
    }

    /**
     * Get the categories registered for the Pages
     *
     * @return array
     */
    private function _getCategories() : array {
        $registryRepository = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity');
        $categoryRegistries = $registryRepository->findBy(['modname' => 'PaustianQuickcheckModule']);
        $baseCategory = $categoryRegistries[0]->getCategory();
        $children = $baseCategory->getChildren();
        return $children->toArray();
    }

    /**
     * utility function to count the number of items held by this module
     * @param int $category
     * @return int
     */
    private function _countItems(int $category) : int {
        if (isset($category) && !empty($category)) {
            $qb = $this->entityManager->createQueryBuilder();

            $qb->select('count(p)')
                    ->from('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', 'p')
                    ->join('p.categories', 'c')
                    ->where('c.category = :categories')
                    ->setParameter('categories', $category)
                    ->andWhere('p.status = ?1' )
                    ->setParameter(1, '0');

            return (int)$qb->getQuery()->getSingleScalarResult();
        }
        $qb = $this->entityManager->createQueryBuilder();
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
    public function createExam(Request $request) : Response {
        //you have to have edit access to do this
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $ret_url = $this->get('router')->generate('paustianquickcheckmodule_user_index', array(), RouterInterface::ABSOLUTE_URL);

        $num_quests = $request->request->get('num_questions', null);
        //now create the quiz

        $em = $this->entityManager;
        // create a QueryBuilder instance
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u')
                ->where('u.status = ?1' )
                ->setParameter(1, '0');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        $questions = $query->getResult();
        //bin the questions into separate categories
        $bin_questions = $this->_binQuestionCategories($questions);
        $quiz_questions = array(); //the array that will hold the questions
        $random_questions = array(); //the random questions from a category
        $examRepo = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');

        foreach ($num_quests as $catid => $number_of_questions) {
            if( (!is_numeric($number_of_questions)) || ($number_of_questions <= 0) ){
                continue;
            }
            if ($number_of_questions > 0) {
                //grab the random keys from the array of questions
                $random_questions = array_rand($bin_questions[$catid], (int)$number_of_questions);
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
            $request->getSession()->getFlashBag()->add('error', $this->trans('You need to pick the number of questions.'));
            return new RedirectResponse($ret_url);
        }
        //shuffle the array to randomize the order in which they get asked.
        shuffle($quiz_questions);
        //build the sq_id array. This is used to grade the quesitons
        $sq_ids = array();
        foreach ($quiz_questions as $question) {
            $sq_ids[] = $question['id'];
        }
        //I need to change this so that it sends back it's own response. What this entails is just getting the data that it
        //needs and then sending it back.
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return $this->render('@PaustianQuickcheckModule/User/quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                    'q_ids' => \serialize($sq_ids),
                    'questions' => $quiz_questions,
                    'return_url' => $ret_url,
                    'print' => false,
                    'exam_name' => $this->trans('Practice Exam')]);
    }

    /**
     * _binQuestionCategories - Given an array of questions bin them into categories based upon their category id.
     * 
     * @param array $questions
     * @return array
     */
    private function _binQuestionCategories(array $questions) : array {
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

    /**
     * _fetch_cat_questions Sort all the questions and put in them into category ids
     *
     * @param array $in_questions
     * @param int $in_cat_id
     * @return array
     */
    private function _fetch_cat_questions(array $in_questions, int $in_cat_id) {
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
     * @param Request $request
     * @param QuickcheckExamEntity|null $exam
     * @param string $return_url
     * @param bool $print
     * @return Response
     * @throws AccessDeniedException
     */
    public function display(Request $request, QuickcheckExamEntity $exam = null, string $return_url = "", bool $print = false) : Response {
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
                throw InvalidParameterException();
            }
            $examQuestions = $examData['questions'];
            $examName = $exam['name'];
            $return_url = $request->request->get('ret_url');
        }
        $sq_ids = array();
        $letters = array();
        $questions = array();
        $repo = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $repo->render_quiz($examQuestions, $questions, $sq_ids, $letters);

        return $this->render('@PaustianQuickcheckModule/User/quickcheck_user_renderexam.html.twig', ['letters' => $letters,
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
     * @param Request $request
     * @param QuickcheckExamEntity|null $exam
     * @return Response
     */
    public function print(Request $request, QuickcheckExamEntity $exam = null) : Response {
        return $this->display($request, $exam, "", true);
    }
    /**
     * @Route("/gradeexam", methods={"POST"})
     * 
     * gradequizAction
     *
     * Here we get the information back from the quiz. We take this, extract the question ids first
     * and then find the right answer to each question. Each question answer comes back as an array, corresponding to
     * the question id. We can then compare this to the correct answer for each type.
     * @param $request
     * @param Request $request
     * @return Response
     */
    public function gradeexam(Request $request,
                                    CurrentUserApiInterface $currentUserApi) : Response {

        if (!$this->hasPermission($this->name . '::', '::', ACCESS_COMMENT)) {
            throw AccessDeniedException();
        }
        $return_url = $request->request->get('ret_url', null);
        $sq_ids = $request->request->get('q_ids', null);
        $q_ids = unserialize($sq_ids);

        $score = 0;
        $display_questions = array();
        $student_answers = array();
        $em = $this->entityManager;
        $examRepo = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $ur_answer = '';
        $catagories = [];

        foreach ($q_ids as $q_id) {
            $student_answer = $request->request->get((string)$q_id, null);
            $question = $em->find('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', $q_id);
            //we need to unpack the question so that we can display it.
            $unpacked_question = $examRepo->unpackQuestion($question, false);
            $cat = $question->getCategories()->first();
            $name = "Uncategorized";
            if($cat !== false){
                $name = $cat->getCategory()->getName();
            }
            $catagories[] = $name;
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
                    if (is_array($student_answer)) {
                        foreach ($student_answer as $checked_item) {
                            $mc_answers = explode('_', $checked_item);
                            //you get points added if this is a correct mark
                            $total += $mc_answers[0];
                            //the student picked a wrong answer, we have to mark it as such.
                            if($mc_answers[0] == 0){$mc_answers[0] = 3;}
                            //mark this position as one that was checked.
                            $ur_answer[$mc_answers[1]] = 1;
                        }
                    }
                    //we need to add the answers the students didn't check and mark them appropriately.
                    foreach($unpacked_question['param'] as $key => $correct_answer){
                        if(!isset($ur_answer[$key])){
                            //the student correctly didn't check this box
                            if($correct_answer == 0){
                                $ur_answer[$key] = 2;
                            } else {
                                //The students should have checked this box
                                $ur_answer[$key] = 4;
                            }
                        }
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
            $display_questions[] = $unpacked_question;
            $student_answers[] = $ur_answer;
            //reset these answers
            $ur_answer = '';
        }
        $catagories = array_unique($catagories);
        $percent = $score / count($q_ids) * 100;
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

        //Record the exam that was taken, the user id, questions, and their score IF a user is logged in
        if($currentUserApi->isLoggedIn()){
            $uid = $currentUserApi->get('uid');
            $gradeRepository = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckGradesEntity');
            $gradeRepository->recordScore($uid, $q_ids, $student_answers, $score, $catagories, new \DateTime());

        }

        return $this->render('@PaustianQuickcheckModule/User/quickcheck_user_gradeexam.html.twig', [
                    'questions' => $display_questions,
                    'score' => $score,
                    'percent' => $percent,
                    'letters' => $letters,
                    'student_answers' => $student_answers]);
    }

    /**
     * @Route("/getpreviewhtml", options={"expose"=true})
     * @Method("POST")
     *
     * Grab all comments associated with this module and item ID and return them to the caller
     * The caller is a javascript, see the javascripts in Resources/public/js directory
     *
     * @param Request $request
     * @return Response|AccessDeniedException
     */

    public function getpreviewhtml(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            return new AccessDeniedException($this->trans('Access forbidden since you cannot read questions.'));
        }
        //fetch the parameters for the question
        $questionText = $request->get('question');
        $answer = $request->get('answer');
        $type = $request->get('type');
        $question = new QuickcheckQuestionEntity();
        $question->setQuickcheckqType((int)$type);
        $question->setQuickcheckqText($questionText);
        $question->setQuickcheckqAnswer($answer);
        //Create the type of object that we need for the renderexam template
        $repo = $this->managerRegistry->getRepository("Paustian\QuickcheckModule\Entity\QuickcheckExamEntity");
        $question = $repo->unpackQuestion($question);
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $response = $this->render('@PaustianQuickcheckModule/User/quickcheck_user_preview.html.twig', ['letters' => $letters,
            'question' => $question]);
        $jsonReply = ['html' => $response->getContent()];

        return  new JsonResponse($jsonReply);
    }

    /**
     * @Route("/viewmyscores", methods={"POST", "GET"})
     *
     * view all exams taken by this person
     *
     * @param Request $request
     * @return Response|AccessDeniedException
     */
    public function viewMyScores(Request $request,
                                CurrentUserApiInterface $currentUserApi){
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            return new AccessDeniedException($this->trans('Access forbidden since you cannot read questions.'));
        }
        if(!$currentUserApi->isLoggedIn()){
            //if you are not logged in, you cannot read scores
            $ret_url = $this->get('router')->generate('paustianquickcheckmodule_user_index', array(), RouterInterface::ABSOLUTE_URL);
            $request->getSession()->getFlashBag()->add('error', $this->trans('You need to pick the number of questions.'));
            return new RedirectResponse($ret_url);
        }

        $uid = $currentUserApi->get('uid');
        $gradeRepository = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckGradesEntity');
        $grades = $gradeRepository->findBy(['uid' => $uid]);
        $gradeArray = [];
        $currentGrade = [];
        foreach($grades as $grade){
            $score = $grade->getScore();
            $question = $grade->getQuestions();
            $currentGrade['numberofquestions'] = sizeof($question[0]);
            $currentGrade['percentage'] = $score/$currentGrade['numberofquestions'] * 100;
            $currentGrade['score'] = $score;
            $currentGrade['id'] = $grade->getId();
            $currentGrade['catagories'] = $grade->getCatagories();
            $currentGrade['catagories'] = $currentGrade['catagories'][0];
            $gradeArray[] = $currentGrade;
        }
        //This is coded, I just now need to implement the html. Make sure you use that fancy table thing
        return $this->render('@PaustianQuickcheckModule/User/quickcheck_user_showgrades.html.twig', [
            'grades' => $gradeArray,
            'showname' => false]);
    }

    /**
     * @Route("/displaypastexam/{grade}")
     *
     * This displays an quiz from the database, or it displays a quiz set up by
     * the student for self study.
     *
     * @param Request $request
     * @param QuickcheckGradesEntity $grade
     * @return Response
     * @throws AccessDeniedException
     */
    public function displayPastExam(Request $request,
                                          int $grade,
                                          CurrentUserApiInterface $currentUserApi){
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            return new AccessDeniedException($this->trans('Access forbidden since you cannot read questions.'));
        }
        if(!$currentUserApi->isLoggedIn()){
            //if you are not logged in, you cannot read scores
            $ret_url = $this->get('router')->generate('paustianquickcheckmodule_user_index', array(), RouterInterface::ABSOLUTE_URL);
            $request->getSession()->getFlashBag()->add('error', $this->trans('You need to pick the number of questions.'));
            return new RedirectResponse($ret_url);
        }
        $gradeRepository = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckGradesEntity');
        $grades = $gradeRepository->find($grade);
        $qIdArray = $grades->getQuestions();
        $qIdArray = $qIdArray[0];
        $answers = $grades->getAnswers();
        $answers = $answers[0];
        $em = $this->managerRegistry->getManager();
        $examRepo = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $score = $grades->getScore();

        foreach($qIdArray as $qId){
            $question = $em->find('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', $qId);
            $display_questions[] = $examRepo->unpackQuestion($question, false);
        }
        $percent = $score / count($qIdArray) * 100;
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return $this->render('@PaustianQuickcheckModule/User/quickcheck_user_gradeexam.html.twig', [
            'questions' => $display_questions,
            'score' => $score,
            'percent' => $percent,
            'letters' => $letters,
            'student_answers' => $answers]);
    }
}

?>
