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

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Quickcheck entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="quickcheck_quest")
 */
class QuickcheckQuestionEntity extends EntityAccess
{
    
    /**
     * question id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=20)
     * @ORM\GeneratedValue(strategy="AUTO")
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
    
    public function __construct() {
        $this->id = 0;
        $this->quickcheckq_type = 0;
        $this->quickcheckq_text = '';
        $this->quickcheckq_answer = '';
        $this->quickcheckq_expan = '';
        $this->quickcheckq_param = '';
    }

    
    public function getId() {
        return $this->id;
    }

    public function getQuickcheckq_type() {
        return $this->quickcheckq_type;
    }

    public function getQuickcheckq_text() {
        return $this->quickcheckq_text;
    }

    public function getQuickcheckq_answer() {
        return $this->quickcheckq_answer;
    }

    public function getQuickcheckq_expan() {
        return $this->quickcheckq_expan;
    }

    public function getQuickcheckq_param() {
        return $this->quickcheckq_param;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setQuickcheckq_type($quickcheckq_type) {
        $this->quickcheckq_type = $quickcheckq_type;
    }

    public function setQuickcheckq_text($quickcheckq_text) {
        $this->quickcheckq_text = $quickcheckq_text;
    }

    public function setQuickcheckq_answer($quickcheckq_answer) {
        $this->quickcheckq_answer = $quickcheckq_answer;
    }

    public function setQuickcheckq_expan($quickcheckq_expan) {
        $this->quickcheckq_expan = $quickcheckq_expan;
    }

    public function setQuickcheckq_param($quickcheckq_param) {
        $this->quickcheckq_param = $quickcheckq_param;
    }
}