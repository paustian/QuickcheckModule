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

use Zikula\Core\ExtensionInstallerInterface;
use Zikula\Core\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DoctrineHelper;
use HookUtil;
use CategoryUtil;
use CategoryRegistryUtil;

class QuickcheckModuleInstaller implements ExtensionInstallerInterface, ContainerAwareInterface {
    
    private $entities = array(
            'Paustian\QuickcheckModule\Entity\QuickcheckExamEntity',
            'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
            'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory'
        );
   
    private $entityManager;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var AbstractBundle
     */
    private $bundle;
    
    
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
            $this->entityManager = $this->container->get('doctrine.entitymanager');
        
        try {
            DoctrineHelper::createSchema($this->entityManager, $this->entities);
        } catch (\Exception $e) {
            print($e->getMessage());
            return false;
        }

        //get ready for using categories
        // create our default category
        $this->_quickcheck_createdefaultcategory();
        //set up the hook provider
        $versionClass = $this->bundle->getVersionClass();
        $version = new $versionClass($this->bundle);
        HookUtil::registerProviderBundles($version->getHookProviderBundles());
        
        
        // Initialisation successful
        return true;
    }

    private function _quickcheck_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global') {

        // create category
        CategoryUtil::createCategory('/__SYSTEM__/Modules', __('PaustianQuickcheckModule'), null, __('Quizzes'), __('Quizzes'));
        // create subcategory
        CategoryUtil::createCategory('/__SYSTEM__/Modules/PaustianQuickcheckModule', 'Chapter 2', null, __('Chapter 2'), __('Initial sub-category created on install'), array('color' => '#cceecc'));
        CategoryUtil::createCategory('/__SYSTEM__/Modules/PaustianQuickcheckModule', 'Chapter 1', null, __('Chapter 1'), __('Initial sub-category created on install'), array('color' => '#99ccff'));
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
    
    public function setBundle(AbstractBundle $bundle)
    {
        $this->bundle = $bundle;
    }
    
    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTranslator($container->get('translator'));
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}

?>