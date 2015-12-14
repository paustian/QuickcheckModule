<?php

/**
 * quickcheck Module
 *
 * A module that you can hook to other modules and provide a quiz function
 *
 * Purpose of file:  Table information for quickcheck module --
 *                   This file contains all information on database
 *                   tables for the module
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      2.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2015 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule;

use Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Zikula\Core\AbstractBundle;
use Zikula\Core\ExtensionInstallerInterface;
use DoctrineHelper;
use HookUtil;
use CategoryUtil;
use CategoryRegistryUtil;

class QuickcheckModuleInstaller extends \Zikula_AbstractInstaller {

    private $entities = array(
            'Paustian\QuickcheckModule\Entity\QuickcheckExamEntity',
            'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
            'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory'
        );
   
    
    /**
     * initialise the quickcheck module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     * This function MUST exist in the pninit file for a module
     *
     * @author       Timothy Paustian
     * @return       bool       true on success, false otherwise
     */
    public function install() {
        // create tables
        

        try {
            DoctrineHelper::createSchema($this->entityManager, $this->entities);
        } catch (\Exception $e) {
            print($e->getMessage());
            return false;
        }

        //get ready for using categories
        // create our default category
        $this->_quickcheck_createdefaultcategory();
        
        
        //HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
        // Initialisation successful
        return true;
    }

    private function _quickcheck_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global') {

        // create category
        CategoryUtil::createCategory('/__SYSTEM__/Modules', $this->__('PaustianQuickcheckModule'), null, $this->__('Quizzes'), $this->__('Quizzes'));
        // create subcategory
        CategoryUtil::createCategory('/__SYSTEM__/Modules/PaustianQuickcheckModule', 'Chapter 2', null, $this->__('Chapter 2'), $this->__('Initial sub-category created on install'), array('color' => '#cceecc'));
        CategoryUtil::createCategory('/__SYSTEM__/Modules/PaustianQuickcheckModule', 'Chapter 1', null, $this->__('Chapter 1'), $this->__('Initial sub-category created on install'), array('color' => '#99ccff'));
        // get the category path to insert Pages categories
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/PaustianQuickcheckModule');
        if ($rootcat) {
            // create an entry in the categories registry to the Main property
            if (!CategoryRegistryUtil::insertEntry('PaustianQuickcheckModule', 'QuickcheckQuestionEntity', 'Main', $rootcat['id'])) {
                throw new \Exception('Cannot insert Category Registry entry.');
            }
        } else {
            throw new NotFoundHttpException('Root category not found.');
        }
        return true;
    }

    /**
     * upgrade the Example module from an old version
     *
     * This function can be called multiple times
     * This function MUST exist in the pninit file for a module
     *
     * @author       Timothy Paustian
     * @return       bool       true on success, false otherwise
     */
    public function upgrade($oldversion) {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.1.0':
            //the category stuff has to be updated.
            //First create the new entity stuff.
            $this->_quickcheck_createdefaultcategory();
            //now shift it over.
            $registry = CategoryRegistryUtil::getRegisteredModuleCategoriesIds('PaustianQuickcheckModule', 'QuickcheckQuestionCategory');
            foreach ($registry as $propname => $regId) {
                $catId = CategoryRegistryUtil::getRegisteredModuleCategory('PaustianQuickcheckModule', 'QuickcheckQuestionCategory', $propName);
                CategoryRegistyUtil::updateEntry($regId, 'PaustianQuickcheckModule', 'QuickcheckQuestionCategory', 'Main', $catId);
            }
        }
        // Update successful
        return true;
    }

    /**
     * delete the Quickcheck module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * This function MUST exist in the pninit file for a module
     *
     * @author       Timothy Paustian
     * @return       bool       true on success, false otherwise
     */
    public function uninstall() {

        try {
            DoctrineHelper::dropSchema($this->entityManager, $this->entities);
        } catch (\PDOException $e) {
            print($e->getMessage());
            return false;
        }

        // Deletion successful
        return true;
    }

}

?>