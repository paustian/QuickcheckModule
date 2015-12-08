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
 * @copyright    Copyright (C) 2015 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule;

use DoctrineHelper;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;

class QuickcheckModuleInstaller extends \Zikula_AbstractInstaller {

    public function setBundle(AbstractBundle $bundle) {
        $this->$bundle = $bundle;
    }

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
        $classes = array(
            'Paustian\\QuickcheckModule\\Entity\\QuickcheckExamEntity',
            'Paustian\\QuickcheckModule\\Entity\\QuickcheckQuestionEntity',
            'Paustian\\QuickcheckModule\\Entity\\QuickcheckQuestionCategory'
        );

        try {
            DoctrineHelper::createSchema($this->entityManager, $classes);
        } catch (\Exception $e) {
            print_r($e);
            return false;
        }

        //get ready for using categories
        // create our default category
        $this->_quickcheck_createdefaultcategory();

        //HookUtil::registerProviderBundles($this->version->getHookProviderBundles());*/
        // Initialisation successful
        return true;
    }

    private function _quickcheck_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global') {

        // create category
        CategoryUtil::createCategory('/__SYSTEM__/Modules', $this->bundle->getName(), null, $this->__('Quickcheck'), $this->__('Quizzes'));
        // create subcategory
        CategoryUtil::createCategory('/__SYSTEM__/Modules/PaustianQuickcheckModule', 'Chatper 1', null, $this->__('Chatper 1'), $this->__('Initial sub-category created on install'), array('color' => '#99ccff'));
        CategoryUtil::createCategory('/__SYSTEM__/Modules/PaustianQuickcheckModule', 'Chapter 2', null, $this->__('Chatper 2'), $this->__('Initial sub-category created on install'), array('color' => '#cceecc'));
        // get the category path to insert Pages categories
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/PaustianQuickcheckModule');
        if ($rootcat) {
            // create an entry in the categories registry to the Main property
            if (!CategoryRegistryUtil::insertEntry($this->bundle->getName(), 'QuickcheckQuestionEntity', 'Main', $rootcat['id'])) {
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
            $registry = CategoryRegistryUtil::getRegisteredModuleCategoriesIds('QuickcheckModule', 'QuickcheckQuestionCategory');
            foreach ($registry as $propname => $regId) {
                $catId = CategoryRegistryUtil::getRegisteredModuleCategory('QuickcheckModule', 'QuickcheckQuestionCategory', $propName);
                CategoryRegistyUtil::updateEntry($regId, 'QuickcheckModule', 'QuickcheckQuestionCategory', 'Main', $catId);
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
        $classes = array(
            'Paustian\\QuickcheckModule\\Entity\\QuickcheckExamEntity',
            'Paustian\\QuickcheckModule\\Entity\\QuickcheckQuestionEntity',
            'Paustian\\QuickcheckModule\\Entity\\QuickcheckQuestionCategory'
        );

        try {
            DoctrineHelper::dropSchema($this->entityManager, $classes);
        } catch (\PDOException $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }

        // Deletion successful
        return true;
    }

}

?>