<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckGradesEntity;



class QuickcheckGradesRepository extends EntityRepository {
    public function recordScore(int $uid,
                                array $display_questions,
                                array $student_answers,
                                float $score) : void {
        //I just need to create the entity and save it. These are never duplicated.
        $gradeResult = new QuickcheckGradesEntity();
        $gradeResult->setUid($uid);
        $gradeResult->setQuestions($display_questions);
        $gradeResult->setAnswers($student_answers);
        $gradeResult->setScore($score);
        $this->_em->persist($gradeResult);
        $this->_em->flush();
    }
}