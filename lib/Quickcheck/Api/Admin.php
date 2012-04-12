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
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class Quickcheck_Api_Admin extends Zikula_AbstractApi {

    /**
     * create a new Example item
     * 
     * @param    $args['name']    name of the exam
     * @param    $args['questions']  an array of the questions ids to ask
     * @return   int              exam ID on success, false on failure
     */
    public function create($args) {
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if (!isset($args['name']) || !isset($args['questions'])) {
            LogUtil::registerArgsError();
            return false;
        }
        // Security check - important to do this as early on as possible to 
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Quickcheck::', "::", ACCESS_ADD)) {
            LogUtil::registerAuthidError();
            return false;
        }
        //if the qeustions are array serialize it
        if (is_array($args['questions'])) {
            $args['questions'] = serialize($args['questions']);
        }

        //insert a new object. The id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'quickcheck_exam')) {
            return LogUtil::registerError(_CREATEFAILED . "insert of strain failed");
        }

        // Return the id of the newly created item to the calling process
        return $args['id'];
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
            LogUtil::registerArgsError();
            return false;
        }
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Quickcheck::', "::", ACCESS_ADD)) {
            LogUtil::registerAuthidError();
            return false;
        }
        //if the questions are array serialize it
        if (is_array($args['q_answer'])) {
            $args['q_answer'] = serialize($args['q_answer']);
        }
        //if the questions are array serialize it
        if (is_array($args['q_param'])) {
            $args['q_param'] = serialize($args['q_param']);
        }

        //insert a new object. The id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'quickcheck_quest')) {
            return LogUtil::registerError(_CREATEFAILED . "insert of question failed");
        }

        // Return the id of the newly created item to the calling process
        return $args['id'];
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
            LogUtil::registerArgsError();
            return false;
        }

        $item = modUtil::apiFunc('quickcheck', 'user', 'get', array('id' => $id));

        if (!$item) {
            LogUtil::registerError(_NOSUCHITEM);
            return false;
        }

        // Security check 
        // In this case we had to wait until we could obtain the item
        // name to complete the instance information so this is the first
        // chance we get to do the check
        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_DELETE)) {
            LogUtil::registerAuthidError();
            return false;
        }
        if (!DBUtil::deleteObjectByID('quickcheck_exam', $args['id'])) {
            return LogUtil::registerError(_DELETEFAILED . "strainID delete");
        }
        // Let any hooks know that we have deleted an item.
        pnModCallHooks('item', 'delete', $id, array('module' => 'quickcheck'));

        // Let the calling process know that we have finished successfully
        return true;
    }

    public function deletequestion($args) {
        // Security check
        // In this case we had to wait until we could obtain the item
        // name to complete the instance information so this is the first
        // chance we get to do the check
        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_DELETE)) {
            LogUtil::registerAuthidError();
            return false;
        }

        $id = $args['id'];
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if ((!isset($id)) ||
                (isset($id) && !is_numeric($id))) {
            LogUtil::registerArgsError();
            return false;
        }

        $item = modUtil::apiFunc('quickcheck', 'user', 'getquestion', array('id' => $id));

        if (!$item) {
            LogUtil::registerError(_NOSUCHITEM);
            return false;
        }


        if (!DBUtil::deleteObjectByID('quickcheck_quest', $args['id'])) {
            return LogUtil::registerError(_DELETEFAILED . "quickcheck delete");
        }

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
            LogUtil::registerArgsError();
            return false;
        }

        // Security check - important to do this as early on as possible to 
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_EDIT)) {
            LogUtil::registerAuthidError();
            return false;
        }
        //if the questions are array serialize it
        if (is_array($args['questions'])) {
            $args['questions'] = serialize($args['questions']);
        }

        if (!DBUtil::updateObject($args, 'quickcheck_exam')) {
            return LogUtil::registerError($this->__("Update of the exam failed."));
        }

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
                || !isset($args['q_answer']) || !isset($args['q_text'])) {
            LogUtil::registerArgsError();
            return false;
        }

        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_EDIT)) {
            LogUtil::registerAuthidError();
            return false;
        }

        //if the questions are array serialize it
        if (is_array($args['q_answer'])) {
            $args['q_answer'] = serialize($args['q_answer']);
        }
        //if the questions are array serialize it
        if (is_array($args['q_param'])) {
            $args['q_param'] = serialize($args['q_param']);
        }

        if (!DBUtil::updateObject($args, 'quickcheck_quest')) {
            return LogUtil::registerError($this->__("Updating the question failed"));
        }
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

        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_ADMIN)) {
            LogUtil::registerAuthidError();
            return false;
        }

        $import_xml = $args['questions'];
        $pattern = "|<question>(.*?)</question>|s";
        //split all the questions into a match array
        preg_match_all($pattern, $import_xml, $matches);
        $q_data = array();

        foreach ($matches[1] as $q_item) {
            //grab the type
            preg_match("|<qtype>(.*?)</qtype>|", $q_item, $q_type);
            //now convert this into the correct number
            switch ($q_type[1]) {
                case 'multichoice':
                    $q_data['q_type'] = Quickcheck_Controller_Admin::_QUICKCHECK_MULTIPLECHOICE_TYPE;
                    break;
                case 'text':
                    $q_data['q_type'] = Quickcheck_Controller_Admin_QUICKCHECK_TEXT_TYPE;
                    break;
                case 'multianswer':
                    $q_data['q_type'] = Quickcheck_Controller_Admin::_QUICKCHECK_MULTIANSWER_TYPE;
                    break;
                case 'matching':
                    $q_data['q_type'] = Quickcheck_Controller_Admin::_QUICKCHECK_MATCHING_TYPE;
                    break;
                case 'truefalse':
                    $q_data['q_type'] = Quickcheck_Controller_Admin::_QUICKCHECK_TF_TYPE;
                    break;
                default:
                    //if we get here there is an issue, throw an error
                    $this->throwNotFound($this->__('Unrecognized question type, was your qtype empty in the xml file?'));
                    break;
            }
            //grab the text of the questsion
            $preg_match = preg_match("|<qtext>(.*?)</qtext>|", $q_item, $q_text);
            $q_data['q_text'] = $q_text[1];
            //grab the explanation
            preg_match("|<qexplanation>(.*?)</qexplanation>|", $q_item, $q_explan);
            $q_data['q_explan'] = $q_explan[1];
            //grab the answer
            preg_match("|<qanswer>(.*?)</qanswer>|", $q_item, $q_answer);
            preg_match("|<qparam>(.*?)</qparam>|", $q_item, $q_param);
            //get the id if it exists
            //we have to process multichoice, multianswer and matching because they are arrays
            if (($q_data['q_type'] == Quickcheck_Controller_Admin::_QUICKCHECK_MULTIPLECHOICE_TYPE) ||
                    ($q_data['q_type'] == Quickcheck_Controller_Admin::_QUICKCHECK_MULTIANSWER_TYPE) ||
                    ($q_data['q_type'] == Quickcheck_Controller_Admin::_QUICKCHECK_MATCHING_TYPE)) {
                $q_data['q_answer'] = serialize(explode('|', $q_answer[1]));
                $q_data['q_param'] = serialize(explode('|', $q_param[1]));
            } else {
                $q_data['q_answer'] = $q_answer[1];
                $q_data['q_param'] = "";
            }
            
            
            //check to see if this items exists (an id came with the item
            //and that id exists in the databse.
            $do_update = false;
            if (preg_match("|<qid>(.*?)</qid>|", $q_item, $q_id)) {
                $q_data['id'] = $q_id[1];
                $item = modUtil::apiFunc('quickcheck', 'user', 'getquestion', array('id' => $q_data['id']));
                $do_update = ($item != false);
            }
            if ($do_update) {

                if (!DBUtil::updateObject($q_data, 'quickcheck_quest')) {
                    return LogUtil::registerError($this->__("insert of question failed"));
                }
            } else {
                if (!DBUtil::insertObject($q_data, 'quickcheck_quest', 'id', true)) {
                    return LogUtil::registerError($this->__("insert of question failed"));
                }
            }
            //void it out to prevent id's being reused.
            $q_data = array();
        }
        return true;
    }

    public function export($args) {
        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_ADMIN)) {
            LogUtil::registerAuthidError();
            return false;
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
                LogUtil::registerArgsError();
                return false;
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
                case Quickcheck_Controller_Admin::_QUICKCHECK_TEXT_TYPE:
                    $type = 'text';
                    $answer = $q_item['q_answer'];
                    $param = $q_item['q_param'];
                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                    $type = 'multichoice';
                    $answer = implode('|', $q_item['q_answer']);
                    $param = implode('|', $q_item['q_param']);
                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_TF_TYPE:
                    $type = 'truefalse';
                    $answer = $q_item['q_answer'];
                    $param = $q_item['q_param'];
                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_MATCHING_TYPE:
                    $type = 'matching';
                    $answer = implode('|', $q_item['q_answer']);
                    $param = implode('|', $q_item['q_param']);
                    break;
                case Quickcheck_Controller_Admin::_QUICKCHECK_MULTIANSWER_TYPE:
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
