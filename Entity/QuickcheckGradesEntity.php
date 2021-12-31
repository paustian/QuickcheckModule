<?php

declare(strict_types=1);

/**
 * Copyright Timothy Paustian 2021
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

/**
 * Quickcheck entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Paustian\QuickcheckModule\Entity\Repository\QuickcheckGradesRepository")
 * @ORM\Table(name="quickcheck_grades")
 */
class QuickcheckGradesEntity extends EntityAccess {
    /**
     * grade id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * user id
     *
     * @ORM\Column(type="integer", length=11)
     */
    private $uid;

    /**
     * question array
     * @ORM\Column(type="array")
     *
     */
    private $questions;

    /**
     * student answer array
     * @ORM\Column(type="array")
     *
     */
    private $answers;

    /**
     * score
     * @ORM\Column(type="float")
     */
    private $score;

    /**
     * Constructor
     */
    public function __construct() {
        $this->uid = 0;
        $this->questions = '';
        $this->answers = '';
        $this->score = 0;
    }

    public function getId() : int {
        return $this->id;
    }

    public function getUid() : int {
        return $this->uid;
    }

    public function getQuestions() : array {
        return $this->quesitons;
    }

    public function getAnswers() : array {
        return $this->answers;
    }

    public function getScore() : float {
        return $this->score;
    }

    public function setId(int $id): void  {
        $this->id = $id;
    }

    public function setUid(int $uid): void  {
        $this->uid = $uid;
    }
    public function setQuestions(array $questions) : void {
        $this->questions = $questions;
    }

    public function setAnswers(array $answers): void {
        $this->answers = $answers;
    }

    public function setScore(float $score) : void {
        $this->score = $score;
    }

}