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
 * @package      Paustian
 * @subpackage   Quickcheck
 * @version      2.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2015 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule;


use Zikula\Core\AbstractExtensionInstaller;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory;

class QuickcheckModuleInstaller extends AbstractExtensionInstaller {
    
    private $entities = array(
            QuickcheckExamEntity::class,
            QuickcheckQuestionEntity::class,
            QuickcheckQuestionCategory::class
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
    public function  install() {
        //Create the tables of the module.
        try {
            $this->schemaTool->create($this->entities);
        } catch (\Exception $e) {
            return false;
        }

        // insert default category
        try {
            $this->createCategoryTree();
        } catch (\Exception $e) {
            $this->addFlash('error', $this->__f('Did not create default categories (%s).', ['%s' => $e->getMessage()]));
        }
        // Initialisation successful
        return true;
    }

    /**
     * create the category tree
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If Root category not found
     * @throws \Exception
     *
     * @return boolean
     */
    private function createCategoryTree()
    {
        $locale = $this->container->get('request_stack')->getCurrentRequest()->getLocale();
        $repo = $this->container->get('zikula_categories_module.category_repository');
        // create quickcheck root category
        $parent = $repo->findOneBy(['name' => 'Modules']);
        $quickcheckRoot = new CategoryEntity();
        $quickcheckRoot->setParent($parent);
        $quickcheckRoot->setName($this->bundle->getName());
        $quickcheckRoot->setDisplay_name([
            $locale => $this->__('Quickcheck', 'paustianquickcheckmodule', $locale)
        ]);
        $quickcheckRoot->setDisplay_desc([
            $locale => $this->__('Quickcheck Questions', 'paustianquickcheckmodule', $locale)
        ]);
        $this->entityManager->persist($quickcheckRoot);

        // create Registry
        $registry = new CategoryRegistryEntity();
        $registry->setCategory($quickcheckRoot);
        $registry->setEntityname('QuickcheckQuestionEntity');
        $registry->setModname($this->bundle->getName());
        $registry->setProperty('Main');
        $this->entityManager->persist($registry);
        $this->entityManager->flush();
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
        switch ($oldversion){
            case 3.0:
                //install status member into QuickcheckQuestion Entity
                $sql = "ALTER TABLE `quickcheck_quest` ADD `status` SMALLINT DEFAULT 0";
                $this->entityManager->getConnection()->exec($sql);
            case 3.1:
                //future upgrades
        }
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
            $this->schemaTool->drop($this->entities);
        } catch (\PDOException $e) {
            return false;
        }

        // Delete any module variables.
        $this->delVars();

        // Deletion successful*/
        return true;
    }

}

?>