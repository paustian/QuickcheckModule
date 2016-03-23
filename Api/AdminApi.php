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

    public function getLinks() {
        $links = array();
        //create exam link
        $links[] = array(
                    'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_edit'),
                    'text' => $this->__('Create Exam'), 
                    'icon' => 'plus');
        $links[] = array(
                    'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_modify'),
                    'text' => $this->__('Mofidy Exam'), 
                    'icon' => 'list');
        //The quesiton editing menu
        $submenulinks = array();
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_edittextquest'),
                'text' => $this->__('Create Text Question')); 
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_edittfquest'),
                'text' => $this->__('Create True/False Question')); 
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_editmcquest'),
                'text' => $this->__('Create Multiple Choice Question')); 
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_editmansquest'),
                'text' => $this->__('Create Mult-Answer Question'));
        $submenulinks[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_editmatchquest'),
                'text' => $this->__('Create Matching Question')); 
        $links[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_editquestions'),
                'text' => $this->__('Edit Questions'), 
                'icon' => 'list', 
                'links' => $submenulinks);
        
        //the import/export menu
        $submenulinks2 = array();
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_importquiz'),
                'text' => $this->__('Import questions from XML file')); 
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_exportquiz'),
                'text' => $this->__('Export questions to XML file')); 
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_categorize'),
                'text' => $this->__('Recategorize questions'));
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_findunanswered'),
                'text' => $this->__('Find unexplained questions'));
        $submenulinks2[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_upgradeoldquestions'),
                'text' => $this->__('Update old questions'));
        $links[] = array(
                'url' => $this->get('router')->generate('paustianquickcheckmodule_admin_importquiz'),
                'text' => $this->__('Question Processing'), 
                'icon' => 'refresh', 
                'links' => $submenulinks2);
        return $links;
    }

}

?>