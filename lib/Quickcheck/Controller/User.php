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
class Quickcheck_Controller_User extends Zikula_AbstractController {

    /**
     * redirect to the view funciton
     */
    public function main() {
        return pnRedirect(pnModURL('quickcheck', 'user', 'view'));
    }

    /**
     * view
     * This routine allows for the user to perform the only function, creating a
     * quiz.
     * Using all (or a subset of) the questions available, create a multiple choice
     * quiz.
     *
     * Params: none
     * Returns: the quiz. This can be graded by the gradequiz funciton below
     */
    public function view($args) {
//securtiy check first
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }
//now display a form for the user to pick out the number of questions from
//each category.
        $render = zikula_View::getInstance('Quickcheck', false);

//the goal here is to get the list of categories.
        $questions = modUtil::apiFunc('quickcheck', 'user', 'getallquestions');

//walk the questions grabbing all the different categories
        $cats = array();
        $curr_ids = array();

        foreach ($questions as $question) {
            $item['id'] = $question['__CATEGORIES__']['Main']['id'];
            if ($item['id'] == '') {
                continue;
            }
//only list each item once
            if (array_search($item['id'], $curr_ids) === false) {
                $curr_ids[] = $item['id'];
                $item['name'] = $question['__CATEGORIES__']['Main']['name'];
                $item['sort'] = $question['__CATEGORIES__']['Main']['sort_value'];
                $cats[] = $item;
            }
        }

//sort the array using usort
        usort($cats, "Quickcheck_Controller_User::sort_cat_array");
        $render->assign('cats', $cats);
// Return the output that has been generated by this function
        return $render->fetch('quickcheck_user_makequiz.htm');
    }

    /**
     * sort_cat_array
     * Sort an array based on the sort value of the array
     * This is used to sort a category array before display
     * Date: July 11 2010
     * @author Timothy Paustian
     * @param array $a one value in the array
     * @param array $b secont value in array to compare
     * @return (0 if same, -1 if b less than a, 1 if b more than a)
     */
    static function sort_cat_array($a, $b) {
        if ($a['sort'] == $b['sort']) {
            return 0;
        }
        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }

    /**
     * sort_by_id
     * This sorts the array of questions based upon what is in the
     * category id (sorts by chapter)
     * Date: July 11 2010
     * @author Timothy Paustian
     * @param array $a one value in the array
     * @param array $b secont value in array to compare
     * @return (0 if same, -1 if b less than a, 1 if b more than a)
     */
    static function sort_by_catid($a, $b) {
//stopped here. I need to sort the question by their cat id, makes it easy for picking them out
        $a_id = $a['__CATEGORIES__']['Main']['sort_value'];
        $b_id = $b['__CATEGORIES__']['Main']['sort_value'];
        if ($a_id == $b_id) {
            return 0;
        }
        return ($a_id < $b_id) ? -1 : 1;
    }

    public function renderquiz($args) {
        $ret_url = pnModURL('quickcheck', 'user', 'view');
//a diff array to get rid of stuff we dont want
        $diff_array = array('num_questions[]' => '', 'total_quests' => '');
        $data = FormUtil::getPassedValue('catagory');
        $total_quests = $data['total_quests'];
        $num_quests = FormUtil::getPassedValue('num_questions');
//now create the quiz
        $quiz_questions = array(); //the array that will hold the questions
//sort the question based upon their category id
        $questions = modUtil::apiFunc('quickcheck', 'user', 'getallquestions');
        usort($questions, Quickcheck_Controller_User::sort_by_catid);
//walk the data array and see where we need questions
        $notice = array();
        foreach ($data as $cat_id => $number) {
            $cat_questions = $this->_fetch_cat_questions($questions, $cat_id);
            $cat_count = count($cat_questions);
            if ($cat_count <= $num_quests[$cat_id]) {
                if ($cat_count < $num_quests[$cat_id]) {
                    $num_quest[$cat_id] = $cat_count;
//remember this for later and notify the user
                    $notice[] = $cat_questions[0]['__CATEGORIES__']['Main']['name'];
                }
//just transfer them all over
                $quiz_questions = array_merge($quiz_questions, $cat_questions);
            } else {
//shuffle the array to randomize which questions get asked
                shuffle($cat_questions);
//move $number of elements onto question array
                $quiz_questions = array_merge($quiz_questions, array_slice($cat_questions, 0, $num_quests[$cat_id]));
            }
        }
//pass it to the display function
        return $this->_display_quiz($quiz_questions, $ret_url, "", $notice);

        return true;
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
     * display item
     *
     * This displays an quiz from the database, or it displays a quiz set up by 
     * the student for self study.
     * 
     * Date: October 3 2010
     * @author Timothy Paustian
     * @param array $args['exam'] the exam that holds the questions
     * @param string $args['ret_url'] the url to return to after the quiz is graded
     * @return the text of the quiz.
     *
     */
    public function display($args) {
// Security check - important to do this as early as possible to avoid
// potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }
        $exam = FormUtil::getPassedValue('exam', isset($args['exam']) ? $args['exam'] : null);
        $return_url = FormUtil::getPassedValue('ret_url', isset($args['ret_url']) ? $args['ret_url'] : null);
        $questions = array();
//grab the questions
        foreach ($exam['questions'] as $quest) {
            $questions[] = modUtil::apiFunc('quickcheck', 'user', 'getquestion', array('id' => $quest));
        }
        return $this->_display_quiz($questions, $return_url, $exam['name']);
    }

    /*
     * _display_quiz
     *
     * A private function that displays the quiz
     * Date: October 3 2010
     * @author Timothy Paustian
     * @param array $questions the questions to render
     * @param string $return_url the return url to go back to once the quiz is graded.
     * @param a text item for feedback to the quiz taker.
     * @return the text of the quiz.
     */

    private function _display_quiz($questions, $return_url, $exam_name, $notice = null) {
//we need to walk questions array and find all the matching questions and randomize the answers
        $total = count($questions);
        $q_ids = array();
        for ($i = 0; $i < $total; $i++) {
            $item = $questions[$i];
            $q_ids[] = $item['id'];
            if ($item['q_type'] == 3) {
//matching question, add a new parameter
                $ran_array = $item['q_answer'];
                shuffle($ran_array);
                $item['ran_array'] = $ran_array;
                $questions[$i] = $item;
            }
        }

        $render = zikula_View::getInstance('Quickcheck', false);

        $q_ids = DataUtil::formatForDisplay(serialize($q_ids));
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $render->assign('letters', $letters);
        $render->assign('q_ids', $q_ids);
        $render->assign('questions', $questions);
        $render->assign('notice', $notice);
        $render->assign('ret_url', $return_url);
        $render->assign('exam_name', $exam_name);

// Return the output that has been generated by this function
        return $render->fetch('quickcheck_user_display.htm');
    }

    /**
     * gradequize
     *
     * Here we get the information back from the quiz. We take this, extract the question ids first
     * and then find the right answer to each question. Each question answer comes back as an array, corresponding to
     * the question id. We can then compare this to the correct answer for each type.
     *
     * @param $args -- nothing really.
     */
    public function gradequiz($args) {

        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }
        $return_url = FormUtil::getPassedValue('ret_url', isset($args['ret_url']) ? $args['ret_url'] : null);
        $q_ids = FormUtil::getPassedValue('q_ids', isset($args['q_ids']) ? $args['q_ids'] : null);
        $q_ids = unserialize($q_ids);

        $score = 0;
        $display_questions = array();

        foreach ($q_ids as $q_id) {
            $student_answer = FormUtil::getPassedValue($q_id, isset($args[$q_id]) ? $args[$q_id] : null);
            $question = modUtil::apiFunc('quickcheck', 'user', 'getquestion', array('id' => $q_id));
            $question['correct'] = false;

            switch ($question['q_type']) {
                case Quickcheck_Controller_Admin::_QUICKCHECK_TEXT_TYPE:
                    $score += 1;
                    $question['correct'] = true;
                    $question['ur_answer'] = $student_answer;
//we don't grade text types
                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_TF_TYPE:
                    if ($student_answer == $question['q_answer']) {
                        $score += 1;
                        $question['correct'] = true;
                    }
                    $question['ur_answer'] = $student_answer;
                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_MATCHING_TYPE:
//The first member of the array is the randomized array that the student saw
                    $matches_array = explode(',', $student_answer[0]);
//push the first one off.
                    array_shift($student_answer);
                    $unscrambled = array();
                    foreach ($student_answer as $number) {
                        $unscrambled[] = $matches_array[$number - 1];
                    }
                    $question['ur_answer'] = $unscrambled;
//grab the correct answers from the question.
                    $correct_answer = $question['q_answer'];
                    $size = count($correct_answer);
//walk the arrays comapring each value. I cannot use a php array
//function because position is important
                    $match_right = 0;
                    for ($i = 0; $i < $size; $i++) {
                        if ($correct_answer[$i] == $unscrambled[$i]) {
                            $match_right++;
                        }
                    }
                    $this_score = $match_right / $size;
                    if ($this_score >= 1) {
                        $question['correct'] = true;
                    }
                    $score += $this_score;

                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_MULTIANSWER_TYPE:
                case Quickcheck_Controller_Admin::_QUICKCHECK_MULTIPLECHOICE_TYPE:
//the student answer containg the position of the values that
//they entered. Award points for correct answers. and subtract for incorrect answers
                    $total = 0;
//the weight given to each answer.
                    $answer_wt = $question['q_param'];
                    $deduction = 100 / count($answer_wt);
                    foreach ($student_answer as $check_mark) {
//you get points added if this is a correct mark
                        $total += $answer_wt[$check_mark];
//but if it's incorrect, then a deduction equal to the
//fract of available answers is taken off.
                        if ($answer_wt[$check_mark] == 0) {
                            $total -= $deduction;
                        }
                    }
                    if ($total >= 100) {
                        $question['correct'] = true;
                    }
                    $score += $total / 100;
                    $question['ur_answer'] = $student_answer;
                    break;
            }
//save the questions in an array for display.
            $display_questions[] = $question;
        }

//score is calculated, now I need to display it with the questions.
        $render = zikula_View::getInstance('Quickcheck', false);
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $render->assign('letters', $letters);
        $render->assign('questions', $display_questions);
        $num_quest = count($display_questions);
        $score_percent = 100 * ($score / $num_quest);
        $render->assign('score', $score);
        $render->assign('score_percent', $score_percent);
        $render->assign('num_quest', $num_quest);
        return $render->fetch('quickcheck_user_gradequiz.htm');
    }

}

?>
