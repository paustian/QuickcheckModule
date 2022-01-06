<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckGradesEntity;


class QuickcheckGradesRepository extends EntityRepository {
    public function recordScore(int $uid,
                                array $display_questions,
                                array $student_answers,
                                float $score,
                                array $categories,
                                \DateTime $date) : void {
        //I just need to create the entity and save it. These are never duplicated.
        $gradeResult = new QuickcheckGradesEntity();
        $gradeResult->setUid($uid);
        $gradeResult->setQuestions($display_questions);
        $gradeResult->setAnswers($student_answers);
        $gradeResult->setScore($score);
        sort($categories, SORT_STRING);
        $gradeResult->setCatagories($categories);
        $gradeResult->setDate($date);
        $this->_em->persist($gradeResult);
        $this->_em->flush();
    }

    //Search for user ids or catagories for exams
    //return the result to the user.
    public function findExams(int $uid, string $category) : array {
        //First find all exams that have been done by the array of users
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from('Paustian\QuickcheckModule\Entity\QuickcheckGradesEntity', 'a');

        if($uid > 0){
            $qb->where($qb->expr()->eq('a.uid', ":uid"))
                ->setParameter("uid", $uid);
        }
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        //if the user has not done any exams, just return null
        if(sizeof($result) == 0){
            return $result;
        }
        //We cannot use the sql to search for array values. We have to talk thorugh them
        //and find the ones that have each category.
        $exams = [];
        if($category === ''){
            $exams = $result;
        } else {
            foreach($result as $exam){
                if(false !== array_search($category, $exam['catagories'])){
                    //we wamt the exam, note it and break the innner loop
                    $exams[] = $exam;
                }
            }
        }
        return $exams;
    }
}