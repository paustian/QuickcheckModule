<?php
/**
 * quickcheck Module
 *
 * The quickcheck module is a hookable module for adding quizzes to the end
 * of other modules
 *
 * Purpose of file:
 *
 * @package      None
 * @subpackage
 * @version      1.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * Populate pntables array for StrainID module
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the pnModDBInfoLoad() API function.
 *
 *
 *

 *
 * @author       Timothy Paustian
 * @version      $Revision: 1 $
 * @return       array       The table information
 **/
function quickcheck_pntables() {
    // Initialise table array
    $pntable = array();

    // Full table definition
    //create a unique name for the table
    $quick_check = DBUtil::getLimitedTablename('quickcheck');
    $pntable['quickcheck_exam'] = DBUtil::getLimitedTablename('quickcheck_exam');
    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user. For this module we need
    //one table to store the information for the strains
    $pntable['quickcheck_exam_column'] = array('id'      => 'id',
            'name' => $quick_check . 'name',
            'questions' => $quick_check . 'questions',
            'art_id' => $quick_check . 'art_id');



    $pntable['quickcheck_exam_column_def'] =    array(  'id'      => 'I(11) AUTOINCREMENT PRIMARY',
                'name' => 'C(255)',
                'questions' => "X DEFAULT ''",
                'art_id'      => 'I(11)');

    
    $pntable['quickcheck_quest'] = DBUtil::getLimitedTablename('quickcheck_quest');
    $pntable['quickcheck_quest_column'] = array('id' => 'id',
            'q_type' => $quick_check . 'q_type',
            'q_text' => $quick_check . 'q_text',
            'q_answer' => $quick_check . 'q_answer',
            'q_explan' => $quick_check . 'q_expan',
            'q_param' => $quick_check . 'q_param');

    $pntable['quickcheck_quest_column_def'] = array('id' => 'I(11) AUTOINCREMENT PRIMARY',
            'q_type' => 'I(11) DEFAULT 0',
            'q_text' => 'X DEFAULT ""',
            'q_answer' => 'X DEFAULT ""',
            'q_explan' => 'X DEFAULT ""',
            'q_param' => "X DEFAULT ''");

    // Enable categorization services
    $pntable['quickcheck_quest_db_extra_enable_categorization'] = true;
    $pntable['quickcheck_quest_primary_key_column'] = 'id';

    ObjectUtil::addStandardFieldsToTableDefinition ($pntable['quickcheck_column'], $quick_check);
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['quickcheck_column_def']);

    // Return the table information
    return $pntable;
}

?>