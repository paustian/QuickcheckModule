<?php

/**
 * Copyright Timothy Paustian 2015
 *
 * This work is contributed to the Zikula Project by Timothy Paustian under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Paustian\QuickcheckModule\Entity;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory as QuickcheckCategoryRelation;
use Paustian\QuickcheckModule\Controller\AdminController;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Quickcheck entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="quickcheck_quest")
 */
class QuickcheckQuestionEntity extends \Zikula\Core\Doctrine\EntityAccess {

    /**
     * question id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=20)
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     */
    private $id;

    /**
     * question type
     *
     * @ORM\Column(type="integer", length=2)
     */
    private $quickcheckqtype;

    /**
     * question text
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckqtext;

    /**
     * question answer
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckqanswer;

    /**
     * question explanation
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckqexpan;

    /**
     * question extra paramaters
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckqparam;

    /**
     * @ORM\OneToMany(targetEntity="Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory",
     *                mappedBy="entity", cascade={"remove", "persist"},
     *                orphanRemoval=true, fetch="EAGER")
     */
    private $categories;

    public function __construct() {
        $this->id = 0;
        $this->quickcheckqtype = 0;
        $this->quickcheckqtext = '';
        $this->quickcheckqanswer = '';
        $this->quickcheckqexpan = '';
        $this->quickcheckqparam = '';
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getQuickcheckqType() {
        return $this->quickcheckqtype;
    }

    public function getQuickcheckqText() {
        return $this->quickcheckqtext;
    }

    public function getQuickcheckqAnswer() {
        return $this->quickcheckqanswer;
    }

    public function getQuickcheckqExpan() {
        return $this->quickcheckqexpan;
    }

    public function getQuickcheckqParam() {
        return $this->quickcheckqparam;
    }

    /**
     * Get page categories
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories() {
        return $this->categories;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setQuickcheckqType($quickcheckqtype) {
        $this->quickcheckqtype = $quickcheckqtype;
    }

    public function setQuickcheckqText($quickcheckqtext) {
        $this->quickcheckqtext = $quickcheckqtext;
    }

    public function setQuickcheckqAnswer($quickcheckqanswer) {
        $this->quickcheckqanswer = $quickcheckqanswer;
    }

    public function setQuickcheckqExpan($quickcheckqexpan) {
        $this->quickcheckqexpan = $quickcheckqexpan;
    }

    public function setQuickcheckqParam($quickcheckqparam) {
        //I need to somehow make this string sql safe.
        $this->quickcheckqparam = $quickcheckqparam;
    }

    /**
     * Set question categories
     * I wonder if I can just set this for 1.4.2
     * @param $categories
     */
    public function setCategories(ArrayCollection $categories) {
        foreach ($this->categories as $categoryAssignment) {
            if (false === $key = $this->collectionContains($categories, $categoryAssignment)) {
                $this->categories->removeElement($categoryAssignment);
            } else {
                $categories->remove($key);
            }
        }
        
        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }
    
    /**
     * Check if a collection contains an element based only on two criteria (categoryRegistryId, categoy).
     * @param $collection
     * @param $element
     * @return bool|int
     */
    private function collectionContains($collection, $element)
    {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var \Zikula\PagesModule\Entity\CategoryEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() == $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() == $element->getCategory()
            ) {

                return $key;
            }
        }

        return false;
    }

    /**
     * @Assert\Callback
     * 
     */
    public function validate(ExecutionContextInterface $context) {

        //Check to make sure the question has an answer
        if ($this->getQuickcheckqText() == "") {
            $context->buildViolation(__('The question text cannot be empty'))
                    ->atPath('quickcheckqtext')
                    ->addViolation();
        }
        //Check to make sure there is an explanation
        if ($this->getQuickcheckqExpan() == "") {
            $context->buildViolation(__('The question explanation cannot be empty'))
                    ->atPath('quickcheckqexpan')
                    ->addViolation();
        }
        //Grab the answer for analysis.
        $answer = $this->getQuickcheckqAnswer();
        $answerData = array();
        switch ($this->getQuickcheckqType()) {
            case AdminController::_QUICKCHECK_TEXT_TYPE:
            case AdminController::_QUICKCHECK_TF_TYPE:
            case AdminController::_QUICKCHECK_MATCHING_TYPE:
                if ($answer == "") {
                    $context->buildViolation(__('The answer to the question cannot be empty'))
                            ->atPath('quickcheckqanswer')
                            ->addViolation();
                }
                break;
            case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                preg_match_all('|(.*?)\|([0-9]{1,3})|', $answer, $answerData);
                $answer_percent = $answerData[2];
                $total_percent = 0;
                $hasOneAnswer = false;
                foreach ($answer_percent as $percent) {
                    //check to make sure there is one and only one answer set at 100 percent
                    //note that if there are two set at 100, the total percent will be over 100
                    $hasOneAnswer = ($hasOneAnswer ? $hasOneAnswer : ($percent == 100));
                    $total_percent += $percent;
                }
                if ($total_percent != 100 || !$hasOneAnswer) {
                    //It has to add to 100% if not, there is an error
                    $context->buildViolation(__('Your answer must have one response that is set to 100% and the others set to 0%'))
                            ->atPath('quickcheckqanswer')
                            ->addViolation();
                }
                break;
            case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                preg_match_all('|(.*?)\|([0-9]{1,3})|', $answer, $answerData);
                $answer_percent = $answerData[2];
                $total_percent = 0;
                foreach ($answer_percent as $percent) {
                    $total_percent += $percent;
                }
                if ($total_percent != 100) {
                    //It has to add to 100% if not, there is an error
                    $context->buildViolation(__('Your answer does not add up to 100%'))
                            ->atPath('quickcheckqanswer')
                            ->addViolation();
                }
                break;
        }

        //I need to add other validations for the other fields.
    }

}
