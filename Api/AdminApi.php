<?php

/**
 * quickcheck Module
 *
 * The quickcheck module is a module for creating quizzes that
 * can be attached to other text modules.
 * 
 * Purpose of file:  Table information for quickcheck module --
 *                   This file contains all information on database
 *                   tables for the module
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      3.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2015 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
namespace Paustian\QuickcheckModule\Api;

use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Paustian\QuickcheckModule\Controller\AdminController;
use SecurityUtil;

class AdminApi extends \Zikula_AbstractApi {
    /**
     * remove a deleted question from an exam
     * @param   the id of the question to delete
     * 
     * @returns true is successful
     */
    
    private function _removeQuestionFromExams($id){
        $exams = modUtil::apiFunc('quickcheck', 'user', 'getall');
        foreach ($exams as $exam) {
            $questions = unserialize($exam['questions']);
            $q_index = array_search($id, $questions);
            //we have to be careful here and use boolean operators
            //$q_index can be 0
            if($q_index == FALSE){
                continue;
            }
            //if we got here, the quesiton is part of the array
            //remove the item
            unset($questions[$q_index]);
            //we need to copy this over again to reset the index. May not be necessary in 
            //this case, but it's nicer to have a continuous index of values.
            $questions = array_values($questions);
            $exam['questions'] = serialize($questions);
            modUtil::apiFunc('quickcheck', 'admin', 'update', $exam);
        }
        return true;
    }
    /**
     * create a new exam item
     * 
     * @param    $args['name']    name of the exam
     * @param    $args['questions']  an array of the questions ids to ask
     * @return   int              exam ID on success, false on failure
     */
    public function create($args) {
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if (!isset($args['quickcheckname']) || !isset($args['quickcheckquestions'])) {
            throw new \InvalidArgumentException(__('Invalid name or question received'));
        }
        
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        
        //if the qeustions are array serialize it
        if (is_array($args['quickcheckquestions'])) {
            $args['quickcheckquestions'] = serialize($args['quickcheckquestions']);
        }
        
        $obj = new QuickcheckExamEntity;
        $obj['quickcheckname'] = $args['quickcheckname'];
        $obj['quickcheckquestions'] = $args['quickcheckquestions'];
        $obj['quickcheckart_id'] = $args['quickcheckart_id'];
        
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
        
        $examId = $obj->getId();

        // Return the id of the newly created item to the calling process
        return $examId;
    }

    /**
     * create a new question
     *
     * @param    $args['q_type']    type of question. It can be 1 of 5 types
     * @param    $args['q_text']    text of question. This is the question that is asked
     * @param    $args['q_answer']  the answer the the question. What goes here depends upon the question
     * @param    $args['q_param']   any other information the question may need
     * @return   int                item ID on success, false on failure
     */
    public function createquestion($args) {

        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if (!isset($args['q_type']) || !isset($args['q_text']) || !isset($args['q_answer'])) {
            throw new \InvalidArgumentException(__('Invalid question type, text, or answer received'));
        }
        
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        
        
        $obj = new QuickcheckQuestionEntity;
        $obj->setQuickcheckqType($args['q_type']);
        $obj->setQuickcheckqText($args['q_text']);
        $obj->setQuickcheckqAnswer($args['q_answer']);
        $obj->setQuickcheckqExpan($args['q_explan']);
        $obj->setQuickcheckqParam($args['q_param']);
        $params = array('modname' => 'PaustianQuickcheckModule', 'id' => $args['__CATEGORIES__']);
        $catEntity = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')->findOneBy($params);
        $obj->setcategories($catEntity);
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
        
        $qId = $obj->getId();

        // Return the id of the newly created item to the calling process
        return $qId;
 }

    /**
     * delete an item
     * 
     * @param    $args['id']   ID of the item
     * @return   bool           true on success, false on failure
     */
    public function delete($args) {
        $id = $args['id'];
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if ((!isset($id)) ||
                (isset($id) && !is_numeric($id))) {
            throw new \InvalidArgumentException(__('Invalid arguments received'));
        }
        // get item
        $item = $this->entityManager->find('QuickcheckModule:QuickcheckExamEntity', $args['id']);

        if (!$item) {
            throw new \InvalidArgumentException(__('There is not exam to delete'));
        }
        
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }
        // keep item to pass it to dispatcher later
        $deletedItem = $item->toArray();
        // Delete the exam
        
        $this->entityManager->remove($item);
        $this->entityManager->flush();
        
        // Let other modules know that we have deleted a group.
        $deleteEvent = new GenericEvent($deletedItem);
        $this->getDispatcher()->dispatch('quickcheck.delete', $deleteEvent);
        
        // Let the calling process know that we have finished successfully
        return true;
    }

    public function deletequestion($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $id = $args['id'];
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if ((!isset($id)) ||
                (isset($id) && !is_numeric($id))) {
           throw new \InvalidArgumentException(__('Invalid id received'));
        }

        $item = $this->entityManager->find('QuickcheckModule:QuickcheckQuestionEntity', $id);

        if (!$item) {
            throw new \InvalidArgumentException(__('There is no question that matches that id to delete'));
        }
        
        
        // Delete the exam
        $this->entityManager->remove($item);
        $this->entityManager->flush();
        
        //we need to work through all the exams and remove that question from them.
        $this->_removeQuestionFromExams($id);
        // Let the calling process know that we have finished successfully
        return true;
    }

    /*
      /**
     * update an item
     * 
     * @param    $args['id']     the ID of the exam
     * @param    $args['questions']    the questions on the exam
     * @param    $args['name']  the exam name
     * @return   bool             true on success, false on failure
     */

    public function update($args) {
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if (!isset($args['name']) || !isset($args['id']) || !isset($args['questions'])) {
            throw new \InvalidArgumentException(__('Name, id or question wrong in update.'));
        }
        
        // get item
        $item = $this->entityManager->find('QuickcheckModule:QuickcheckExamEntity', $args['id']);
        if (!$item) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', $args['id'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        //if the questions are array serialize it
        if (is_array($args['questions'])) {
            $args['questions'] = serialize($args['questions']);
        }
        
        // Update the item
        $item->merge($args);
        $this->entityManager->flush();
        
        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update new question
     *
     * @param    $args['q_type']    type of question. It can be 1 of 5 types
     * @param    $args['q_text']    text of question. This is the question that is asked
     * @param    $args['q_answer']  the answer the the question. What goes here depends upon the question
     * @param    $args['q_param']   any other information the question may need
     * @param    $args['id']        item ID on success, false on failure
     * @return   bool               true on success, false on failure
     */
    public function updatequestion($args) {
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        //note that q_param is optional
        if (!isset($args['q_type']) || !isset($args['id'])
                || !isset($args['q_answer']) || !isset($args['$q_text'])) {
            throw new \InvalidArgumentException(__('question wrong in update update.'));
        }

        // get item
        $item = $this->entityManager->find('QuickcheckModule:QuickcheckExamEntity', $args['id']);
        if (!$item) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::' . $args['id'], ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        //if the questions are array serialize it
        if (is_array($args['$quickcheckq_answer'])) {
            $args['$quickcheckq_answer'] = serialize($args['$quickcheckq_answer']);
        }
        //if the questions are array serialize it
        if (is_array($args['quickcheckq_param'])) {
            $args['quickcheckq_param'] = serialize($args['quickcheckq_param']);
        }
        
        // Update the item
        $item->merge($args);
        $this->entityManager->flush();
        
        // Let the calling process know that we have finished successfully
        return true;
    }

    /*
     * import
     * Give an xml list, take this list and import it into the quesitons list
     * Here is the xml that it will parse....
     *
     * <questiondoc>
     * <question>
      <qtype>quesiton type</qtype> options are multichoice, truefalse, multianswer, matching, text
      <qtext>Text of questions goes here</qtext> text
      <qanswer>Answer of question goes here</qanswer> answer is either text or a comma separated list "first answer", "second answer", "etc"
      <qexplanation>An explanation of the answer goes here.</qexplanation> text
      <qparam>any extra information</qparam> can be comman separated list of other things.
      </question>
      <question>

      <qtype>multichioce</qtype>
      <qtext>Text of questions goes here</qtext>
      <qanswer>Answer of question goes here</qanswer>
      <qexplanation>An explanation of the answer goes here.</qexplanation>
      <qparam>any extra information</qparam>
      </question>

      </questiondoc>
     *
     *  @param      $args['questions']  The xml to import
     *  @returns    true on successful import, false otherwiese.
     */

    public function import($args) {

        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        
        return true;
    }

    public function export($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::' . $args['id'], ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        //check your arguments
        if (!isset($args['export_all'])) {
            $args['export_all'] = 'off';
        }

        $export_all = $args['export_all'];

        $questions = array();
        if ($export_all == 'on') {
            $questions = modUtil::apiFunc('quickcheck', 'user', 'getallquestions');
        } else {
            if (!isset($args['q_ids'])) {
                throw new \InvalidArgumentException(__('No ids were sent to export.'));
            }
            $q_ids = $args['q_ids'];
            foreach ($q_ids as $question_id) {
                $question = modUtil::apiFunc('quickcheck', 'user', 'getquestion', array('id' => $question_id));
                $questions[] = $question;
            }
        }
        $q_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<questiondoc>";

        foreach ($questions as $q_item) {
            //open the question
            $q_xml .= "<question>\n";
            $answer = "";
            $param = "";
            $type = "";
            //write the type
            switch ($q_item['q_type']) {
                case AdminController::_QUICKCHECK_TEXT_TYPE:
                    $type = 'text';
                    $answer = $q_item['q_answer'];
                    $param = $q_item['q_param'];
                    break;
                case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                    $type = 'multichoice';
                    $answer = implode('|', $q_item['q_answer']);
                    $param = implode('|', $q_item['q_param']);
                    break;
                case AdminController::_QUICKCHECK_TF_TYPE:
                    $type = 'truefalse';
                    $answer = $q_item['q_answer'];
                    $param = $q_item['q_param'];
                    break;
                case AdminController::_QUICKCHECK_MATCHING_TYPE:
                    $type = 'matching';
                    $answer = implode('|', $q_item['q_answer']);
                    $param = implode('|', $q_item['q_param']);
                    break;
                case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                    $type = 'multianswer';
                    $answer = implode('|', $q_item['q_answer']);
                    $param = implode('|', $q_item['q_param']);
                    break;
            }
            //write the text of the quetsion
            $q_xml .= "\t<qid>" . $q_item['id'] . "</qid>\n";
            $q_xml .= "\t<qtype>$type</qtype>\n";
            $q_xml .= "\t<qtext>" . $q_item['q_text'] . "</qtext>\n";
            $q_xml .= "\t<qanswer>$answer</qanswer>\n";
            $q_xml .= "\t<qexplanation>" . $q_item['q_explan'] . "</qexplanation>\n";
            $q_xml .= "\t<qparam>$param</qparam>\n";
            $q_xml .= "</question>\n";
        }
        $q_xml .= "</questiondoc>\n";

        return $q_xml;
    }

}
?>