<?php

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;

class QuickcheckQuestionRepository extends EntityRepository {

    public function getSearchResult($words, $searchType){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from('PaustianQuickcheckModule:QuckcheckQuestionEntity', 'a');
        $count = count($words);
        for($i = 0; $i < $count; $i++) {
            if ($searchType == 'AND') {
                $qb->andWhere('a.quickcheckqtext LIKE :word' . $i);
                $qb->andWhere('a.quickcheckqanswer LIKE :word' . $i);
            } else {
                $qb->orWhere('a.quickcheckqtext LIKE :word'. $i);
                $qb->orWhere('a.quickcheckqanswer LIKE :word' . $i);
            }
            $qb->setParameter('word'. $i, '%' . $words[$i] . '%');
        }
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }
}