<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Controller\AdminController;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;


class QuickcheckExamRepository extends EntityRepository {

    /**
     * get_all_exams
     * return all the exams in the database
     *
     * @return array
     */

    public function get_all_exams() : array {
        // create a QueryBuilder instance
        $qb = $this->_em->createQueryBuilder();

        // add select and from params
        $qb->select('u')
            ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u');
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * get the exam object for the referring id
     * @param $art_id
     * @return array
      */
    public function get_exam($art_id) : array {
        $result = $this->findOneByQuickcheckrefid($art_id);
        return $result;
    }
    
    
    /*
     * _render_quiz
     *
     * A function that calculates all the data for displaying the quiz.
     *
     * @param array $examQuestions
     * @param array $questions
     * @param array $sq_ids
     * @param array $letters
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    
   
    public function render_quiz(array $examQuestions, array &$questions, array &$sq_ids, array &$letters) : void {

        //grab the questions
        $em = $this->_em;
        if($examQuestions != null){
            foreach ($examQuestions as $quest) {
                $question = $em->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $quest);
                $questions[] = $this->unpackQuestion($question);
            }
            //we need to walk questions array and find all the matching questions and randomize the answers
            $total = count($questions);
            $q_ids = array();
            for ($i = 0; $i < $total; $i++) {
                $item = $questions[$i];
                $q_ids[] = $item['id'];
                if ($item['q_type'] == 3) {
                    //matching question, add a new parameter
                    $ran_array = $item['q_answer'];
                    shuffle($ran_array);
                    $item['ran_array'] = $ran_array;
                    $questions[$i] = $item;
                }
            }

            $sq_ids = serialize($q_ids);
        } else {
            $sq_ids = "";
        }

        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    }

    /**
     * unpackQuestion - Unpack the question and prepare it for display.
     *
     * @param QuickcheckQuestionEntity $in_question
     * @param bool $shuffle
     * @return QuickcheckQuestionEntity
     */
    public function unpackQuestion(QuickcheckQuestionEntity $in_question, bool $shuffle = true) : QuickcheckQuestionEntity {
        $type = $in_question->getQuickcheckqType();
        //We need to unpack this a bit to prepare it for display
        //We parse out the correct answer and put those in the param variable of the class
        if (($type == Admincontroller::_QUICKCHECK_MULTIPLECHOICE_TYPE) ||
                ($type == Admincontroller::_QUICKCHECK_MATCHING_TYPE) ||
                ($type == Admincontroller::_QUICKCHECK_MULTIANSWER_TYPE)) {
            $qAnswer = $in_question->getQuickcheckqAnswer();
            preg_match_all("|(.*)\|(.*)|", $qAnswer, $matches);
            $in_question->setQuickcheckqAnswer($matches[1]);
            if (($type == Admincontroller::_QUICKCHECK_MATCHING_TYPE) && $shuffle) {
                $param = array();
                $orig_array = $matches[2];
                $shuff_array = array_keys($matches[2]);
                shuffle($shuff_array);
                foreach ($shuff_array as $item) {
                    //The array item
                    $param[0][] = $orig_array[$item];
                    //It's original position.
                    $param[1][] = $item;
                }
                $in_question->setQuickcheckqParam($param);
            } else {
                $in_question->setQuickcheckqParam($matches[2]);
            }
        }

        return $in_question;
    }
    
}