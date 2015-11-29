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
 * @ORM\Table(name="quickcheck_exam")
 */
class QuickcheckExamEntity extends EntityAccess {

    /**
     * exam id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=20)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * exam name
     *
     * @ORM\Column(type="string", length=255)
     */
    private $quickcheckname;

    /**
     * question list
     * 
     * @ORM\Column(type="text")
     */
    private $quickcheckquestions;

    /**
     * article id that matches this exam
     *
     * @ORM\Column(type="integer", length=20)
     */
    private $quickcheckart_id;

    public function getId() {
        return $this->id;
    }

    public function getQuickcheckname() {
        return $this->quickcheckname;
    }

    public function getQuickcheckquestions() {
        return $this->quickcheckquestions;
    }

    public function getQuickcheckart_id() {
        return $this->quickcheckart_id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setQuickcheckname($quickcheckname) {
        $this->quickcheckname = $quickcheckname;
    }

    public function setQuickcheckquestions($quickcheckquestions) {
        $this->quickcheckquestions = $quickcheckquestions;
    }

    public function setQuickcheckart_id($quickcheckart_id) {
        $this->quickcheckart_id = $quickcheckart_id;
    }

}
