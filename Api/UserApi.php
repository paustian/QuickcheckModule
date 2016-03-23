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
namespace Paustian\QuickcheckModule\Api;

use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use SecurityUtil;

/**
 * get all strains in database items
 * 
 * @return   array   array of items, or false on failure
 */
class UserApi extends \Zikula_AbstractApi {
    
    public function displayHookInterface(){
        
    }
}

?>