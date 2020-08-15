<?php

declare(strict_types=1);

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

use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Quickcheck entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Paustian\QuickcheckModule\Entity\Repository\QuickcheckExamRepository")
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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * 
     */
    private $quickcheckname;

    /**
     * question list
     * 
     * @ORM\Column(type="array")
     * 
     */
    private $quickcheckquestions;

    /**
     * article id that matches this exam
     *
     * @ORM\Column(type="integer", length=20)
     */
    private $quickcheckrefid;

    /**
     * Constructor 
     */
    public function __construct() {
        $this->quickcheckname = '';
        $this->quickcheckref_id = 0;
    }
    
    public function getId() : int {
        return $this->id;
    }

    public function getQuickcheckname() : string {
        return $this->quickcheckname;
    }

    public function getQuickcheckquestions() : array {
        return $this->quickcheckquestions;
    }

    public function getQuickcheckrefid() : int {
        return $this->quickcheckref_id;
    }

    public function setId(int $id): void  {
        $this->id = $id;
    }

    public function setQuickcheckname(string $quickcheckname) : void{
        $this->quickcheckname = $quickcheckname;
    }

    public function setQuickcheckquestions(array $quickcheckquestions): void {
        $this->quickcheckquestions = $quickcheckquestions;
    }

    public function setQuickcheckrefid(int $quickcheckrefid) : void {
        $this->quickcheckrefid = $quickcheckrefid;
    }

}
