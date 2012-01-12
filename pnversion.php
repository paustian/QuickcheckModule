<?php
/**
 * quickcheck Module
 *
 * The quickcheck module is a module for adding short quizzes to the
 * end of modules. This is idea for modules that display information
 * that you then want to test comprehension on.
 * 
 * Purpose of file:  Table information for quickcheck module --
 *                   Version information.
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      1.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

// The following information is used by the Modules module 
// for display and upgrade purposes
$modversion['name']           = __('Quickcheck');
// the version string must not exceed 10 characters!
$modversion['version']        = '1.0';
$modversion['description']    = __('A hookable module for displaying quizzes or exams as part of other modules.');
$modversion['displayname']    = __('Quickcheck');

// The following in formation is used by the credits module
// to display the correct credits
$modversion['changelog']      = '';
$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['license']        = '';
$modversion['official']       = '1.0';
$modversion['author']         = 'Timothy Paustian';
$modversion['contact']        = 'http://inst.bact.wisc.edu';


// This one adds the info to the DB, so that users can click on the 
// headings in the permission module
$modversion['securityschema'] = array('exam::question' => 'exam id (int)::question id (int)');

?>