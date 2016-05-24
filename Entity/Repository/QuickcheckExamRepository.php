<?php

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Controller\AdminController;
use DataUtil;

class QuickcheckExamRepository extends EntityRepository {
   
    /**
     * get the exam object for the referring id
     * 
     * @param number $art_id
     * @return QuickcheckExamEntity
     */
    public function get_exam($art_id){
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u');
        $qb->where('u.quickcheckrefid = ?1');
        $qb->setParameter(1, $art_id);
        $query = $qb->getQuery();
        $result = $query->execute();
        //don't fail if we don't find it. a null result is ok
        if(empty($result)){
            return false;
        }
        return $result[0];
    }
    
    
    /*
     * _render_quiz
     *
     * A function that calculates all the data for displaying the quiz.
     * Date: 016
     * @author Timothy Paustian
     * 
     * @param the exam to prep questions for
     * @param array questions the questions to render
     * @param array sq_ids
     * 
     * @return the text of the quiz.
     */
    
   
    public function render_quiz($examQuestions, &$questions, &$sq_ids, &$letters) {
        //grab the questions
        $em = $this->_em;
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

        $sq_ids = DataUtil::formatForDisplay(serialize($q_ids));
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    }
    
    public function unpackQuestion($in_question, $shuffle = true) {
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