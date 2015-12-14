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
     * @Assert\NotBlank()
     */
    private $quickcheckq_type;

    /**
     * question text
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $quickcheckq_text;

    /**
     * question answer
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $quickcheckq_answer;

    /**
     * question explanation
     * 
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
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
        if($this->_isserialized($this->quickcheckq_answer)){
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
        if($this->_isserialized($this->quickcheckq_param)){
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
            $quickcheckq_answer = serialize($quickcheckq_answer);
        }
        $this->quickcheckq_answer = $quickcheckq_answer;
    }

    public function setQuickcheckqExpan($quickcheckq_expan) {
        $this->quickcheckq_expan = $quickcheckq_expan;
    }

    public function setQuickcheckqParam($quickcheckq_param) {
        //convert these to string before saving them
        if (is_array($quickcheckq_param)) {
            $quickcheckq_param = serialize($quickcheckq_param);
        }
        $this->quickcheckq_param = $quickcheckq_param;
    }

    /**
     * Set page categories
     *
     * @param $categories
     */
    public function setCategories($categories) {
        $this->categories = new ArrayCollection();
        foreach ($categories as $regId => $category) {
            if ($category instanceof ArrayCollection) {
                // a result of multiple select box
                foreach ($category as $element) {
                    $this->categories[] = new QuickcheckCategoryRelation($regId, $element, $this);
                }
            } else {
                // a normal select box
                $this->categories[] = new QuickcheckCategoryRelation($regId, $category, $this);
            }
        }
    }

}
