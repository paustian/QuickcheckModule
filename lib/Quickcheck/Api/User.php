1<?php
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

/**
 * get all strains in database items
 * 
 * @return   array   array of items, or false on failure
 */
class Quick_Api_User extends Zikula_AbstractApi{

    public function getall() {
        $items = array();

        //security check, almost anyone can see this
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        $items = DBUtil::selectObjectArray('quickcheck_exam');

        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }


        // Return the items
        return $items;
    }

    /**
     * get a specific exam. You can grab them by id or article id
     * 
     * @param    $args['id']  id of example item to get
     * @return   array         item array, or false on failure
     */
    public function get($args) {
        if (isset($args['id'])) {
            $id = $args['id'];
        }
        if (isset($args['art_id'])) {
            $art_id = $args['art_id'];
        }

        if (isset($id) && is_numeric($id)) {
            $item = DBUtil::selectObjectByID('quickcheck_exam', $id);
        } else if (isset($art_id) && is_numeric($art_id)) {
            $item = DBUtil::selectObjectByID('quickcheck_exam', $art_id, 'art_id');
        } else {
            LogUtil::registerArgsError();
            return false;
        }

        if ($item != false) {
            $item['questions'] = unserialize($item['questions']);
        }
        //no check for failure. We just return false and the calling
        //function has to deal with it. It's fine for this to fail, because
        //often the hooked module will not have an exam attached.
        // Return the items
        return $item;
    }

    /**
     * get all questions
     *  @return array   all of the questions in the module.
     *
     */
    public function getallquestions($args) {
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return false;
        }
        if (isset($args['missing_explan']) && $args['missing_explan']) {
            //create a where clause to get only empty explanations
            $pntable = & pnDBGetTables();
            $question_column = &$pntable['quickcheck_quest_column'];
            $where = "WHERE " . $question_column['q_explan'] . " IS NULL OR " . $question_column['q_explan'] . " = '' AND ("
                    . $question_column['q_type'] . " = 4 OR " . $question_column['q_type'] . " = 1)";

            $items = DBUtil::selectObjectArray('quickcheck_quest', $where);
        } else {
            $items = DBUtil::selectObjectArray('quickcheck_quest');
            //we don't log an error here since its ok to not get any questions.
        }

        //we need to unserialize all question params and answers.
        $item_count = count($items);
        for ($i = 0; $i < $item_count; $i++) {
            $answer = @unserialize($items[$i]['q_answer']);
            if ($answer !== false) {
                $items[$i]['q_answer'] = $answer;
            }

            $param = @unserialize($items[$i]['q_param']);
            if ($param !== false) {
                $items[$i]['q_param'] = $param;
            }
        }

        // Return the items
        return $items;
    }

    public function getquestion($args) {
        $item = false;
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return $item;
        }
        $id = FormUtil::getPassedValue('id', isset($args['id']) ? $args['id'] : null);

        if (!isset($id) || !is_numeric($id)) {
            LogUtil::registerArgsError();
            return false;
        }

        $item = DBUtil::selectObjectByID('quickcheck_quest', $id);
        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        $answer = @unserialize($item['q_answer']);
        if ($answer !== false)
            $item['q_answer'] = $answer;
        $param = @unserialize($item['q_param']);
        if ($param !== false)
            $item['q_param'] = $param;
        // Return the items

        return $item;
    }

    /**
     * utility function to count the number of items held by this module
     * 
     * @return   integer   number of items held by this module
     */
    public function countitems() {
        
    }

}
?>