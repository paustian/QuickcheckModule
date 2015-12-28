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

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\Common\Collections\ArrayCollection;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory as QuickcheckCategoryRelation;
use Paustian\QuickcheckModule\Controller\AdminController;
use Doctrine\ORM\Mapping as ORM;

/**
 * Quickcheck entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="quickcheck_quest")
 */
class QuickcheckQuestionEntity extends EntityAccess {

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
    private $quickcheckq_type;

    /**
     * question text
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckq_text;

    /**
     * question answer
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckq_answer;

    /**
     * question explanation
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckq_expan;

    /**
     * question extra paramaters
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckq_param;

    /**
     * @ORM\OneToMany(targetEntity="Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory",
     *                mappedBy="entity", cascade={"all"},
     *                orphanRemoval=true, fetch="EAGER")
     */
    private $categories;

    private function _isserialized($data) {
        return (is_string($data) && preg_match("#^((N;)|((a|O|s):[0-9]+:.*[;}])|((b|i|d):[0-9.E-]+;))$#um", $data));
    }

    public function __construct() {
        $this->id = 0;
        $this->quickcheckq_type = 0;
        $this->quickcheckq_text = '';
        $this->quickcheckq_answer = '';
        $this->quickcheckq_expan = '';
        $this->quickcheckq_param = '';
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getQuickcheckqType() {
        return $this->quickcheckq_type;
    }

    public function getQuickcheckqText() {
        return $this->quickcheckq_text;
    }

    public function getQuickcheckqAnswer() {
        if ($this->_isserialized($this->quickcheckq_answer)) {
            $return_answer = unserialize($this->quickcheckq_answer);
        } else {
            $return_answer = $this->quickcheckq_answer;
        }
        return $return_answer;
    }

    public function getQuickcheckqExpan() {
        return $this->quickcheckq_expan;
    }

    public function getQuickcheckqParam() {
        if ($this->_isserialized($this->quickcheckq_param)) {
            $return_answer = unserialize($this->quickcheckq_param);
        } else {
            $return_answer = $this->quickcheckq_param;
        }
        return $return_answer;
    }

    /**
     * Get page categories
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories() {
        $categories = array();
        /** @var \Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity $catRelation */
        foreach ($this->categories as $catRelation) {
            $registryId = $catRelation->getCategoryRegistryId();
//            if (is_array($catRelation)) {
            if (!isset($categories[$registryId])) {
                $categories[$registryId] = new ArrayCollection();
            }
            $categories[$registryId]->add($catRelation->getCategory());
//            } else {
//                $categories[$registryId] = $catRelation->getCategory();
//            }
        }
        return $categories;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setQuickcheckqType($quickcheckq_type) {
        $this->quickcheckq_type = $quickcheckq_type;
    }

    public function setQuickcheckqText($quickcheckq_text) {
        $this->quickcheckq_text = $quickcheckq_text;
    }

    public function setQuickcheckqAnswer($quickcheckq_answer) {
        //convert these to string before saving them
        if (is_array($quickcheckq_answer)) {
            $quickcheckq_answer = mysql_real_escape_string(serialize($quickcheckq_answer));
        }
        $this->quickcheckq_answer = $quickcheckq_answer;
    }

    public function setQuickcheckqExpan($quickcheckq_expan) {
        $this->quickcheckq_expan = $quickcheckq_expan;
    }

    public function setQuickcheckqParam($quickcheckq_param) {
        //convert these to string before saving them
        if (is_array($quickcheckq_param)) {
            $quickcheckq_param = mysql_real_escape_string(serialize($quickcheckq_param));
        }
        //I need to somehow make this string sql safe.
        $this->quickcheckq_param = $quickcheckq_param;
    }

    /**
     * Set question categories
     *
     * @param $categories
     */
    public function setCategories($categories) {
        $this->categories = new ArrayCollection();
        foreach ($categories as $regId => $category) {
            if ($category instanceof ArrayCollection) {
                // a result of multiple select box
                //We need to delete any entries in the table.
                foreach ($category as $element) {
                    $this->categories[] = new QuickcheckCategoryRelation($regId, $element, $this);
                }
            } else {
                // a normal select box
                $catItem = array_shift($category);
                $this->categories[] = new QuickcheckCategoryRelation($regId, $catItem, $this);
            }
        }
    }

    /**
     * @Assert\Callback
     * 
     */
    public function validate(ExecutionContextInterface $context) {

        //Check to make sure the question has an answer
        if ($this->getQuickcheckqText() == "") {
            $context->buildViolation(__('The question text cannot be empty'))
                    ->atPath('quickcheckq_text')
                    ->addViolation();
        }
        //Check to make sure there is an explanation
        if ($this->getQuickcheckqExpan() == "") {
            $context->buildViolation(__('The question explanation cannot be empty'))
                    ->atPath('quickcheckq_expan')
                    ->addViolation();
        }
        //Grab the answer for analysis.
        $answer = $this->getQuickcheckqAnswer();
        $answerData = array();
        switch ($this->getQuickcheckqType()) {
            case AdminController::_QUICKCHECK_TEXT_TYPE:
            case AdminController::_QUICKCHECK_TF_TYPE:
                if ($answer == "") {
                    $context->buildViolation(__('The answer to the question cannot be empty'))
                            ->atPath('quickcheckq_answer')
                            ->addViolation();
                }
                break;
            case AdminController::_QUICKCHECK_MATCHING_TYPE:
                preg_match_all('|(.*?)\|(.*)|', $answer, $answerData);
                if(empty($answerData[1]) || empty($answer[2])){
                    $context->buildViolation(__('The answer to the question cannot be empty'))
                            ->atPath('quickcheckq_answer')
                            ->addViolation();
                }
                $this->setQuickcheckqAnswer($answerData[1]);
                $this->setQuickcheckqParam($answerData[2]);
                break;
            case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                preg_match_all('|(.*?)\|([0-9]{1,3})|', $answer, $answerData);
                $this->setQuickcheckqAnswer($answerData[1]);
                $this->setQuickcheckqParam($answerData[2]);
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
                            ->atPath('quickcheckq_answer')
                            ->addViolation();
                }
                break;
            case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                preg_match_all('|(.*?)\|([0-9]{1,3})|', $answer, $answerData);
                $this->setQuickcheckqAnswer($answerData[1]);
                $this->setQuickcheckqParam($answerData[2]);
                $answer_percent = $answerData[2];
                $total_percent = 0;
                foreach ($answer_percent as $percent) {
                    $total_percent += $percent;
                }
                if ($total_percent != 100) {
                    //It has to add to 100% if not, there is an error
                    $context->buildViolation(__('Your answer does not add up to 100%'))
                            ->atPath('quickcheckq_answer')
                            ->addViolation();
                }
                break;
        }

        //I need to add other validations for the other fields.
    }

}
