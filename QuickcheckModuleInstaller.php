<?php

declare(strict_types=1);

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


use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\HookBundle\Category\CategoryInterface;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory;
use Paustian\QuickcheckModule\Entity\QuickcheckGradesEntity;

class QuickcheckModuleInstaller extends AbstractExtensionInstaller {
    
    private $entities = array(
            QuickcheckExamEntity::class,
            QuickcheckQuestionEntity::class,
            QuickcheckQuestionCategory::class,
            QuickcheckGradesEntity::class
        );

    //the interface to the category to set up categories for the module.
    private $catInterface;

    public function __construct(AbstractExtension $extension,
                                ManagerRegistry $managerRegistry,
                                SchemaHelper $schemaTool,
                                RequestStack $requestStack,
                                TranslatorInterface $translator,
                                VariableApiInterface $variableApi,
                                CategoryRepositoryInterface $inCatInterface)
    {
        $this->catInterface = $inCatInterface;
        parent::__construct($extension, $managerRegistry, $schemaTool, $requestStack, $translator, $variableApi);
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
    public function  install() : bool {
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
            $this->addFlash('error', $this->trans('Did not create default categories (%message%).', ['%message%' => $e->getMessage()]));
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
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        // create quickcheck root category
        $parent = $this->catInterface->findOneBy(['name' => 'Modules']);
        $quickcheckRoot = new CategoryEntity();
        $quickcheckRoot->setParent($parent);
        $quickcheckRoot->setName($this->name);
        $quickcheckRoot->setDisplayName([
            $locale => $this->trans('Quickcheck', [], $locale)
        ]);
        $quickcheckRoot->setDisplayDesc([
            $locale => $this->trans('Quickcheck Questions', [], $locale)
        ]);
        $this->entityManager->persist($quickcheckRoot);

        // create Registry
        $registry = new CategoryRegistryEntity();
        $registry->setCategory($quickcheckRoot);
        $registry->setEntityname('QuickcheckQuestionEntity');
        $registry->setModname($this->name);
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
    public function upgrade($oldversion) :bool {
        switch ($oldversion){
            case "3.0":
                //install status member into QuickcheckQuestion Entity
                $sql = "ALTER TABLE `quickcheck_quest` ADD `status` SMALLINT DEFAULT 0";
                $this->entityManager->getConnection()->exec($sql);
            case "3.1":
            case "3.2/1":
                //future upgrades
            case "4.0.6":
                //install the grades table
                $sql = "CREATE TABLE `quickcheck_grades` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `uid` int(11) NOT NULL,
                          `questions` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
                          `answers` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
                          `catagories` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
                          `score` double NOT NULL,
                          `date` datetime NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                $this->entityManager->getConnection()->exec($sql);
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
    public function uninstall() : bool {

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