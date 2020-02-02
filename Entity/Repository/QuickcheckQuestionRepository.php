<?php

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;

class QuickcheckQuestionRepository extends EntityRepository {

    public function getSearchResults($words, $searchType){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a.id', 'a.quickcheckqtype', 'a.quickcheckqtext')
            ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'a');

        $count = count($words);
        switch($searchType){
            case 'AND':
                for($i = 0; $i < $count; $i++) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->like('a.quickcheckqtext', ':word' . $i),
                            $qb->expr()->like('a.quickcheckqanswer', ':word' . $i)
                        )
                    );
                    $qb->setParameter('word'. $i, '%' . $words[$i] . '%');
                }
                break;
            case 'OR':
                for($i = 0; $i < $count; $i++) {
                    $qb->orWhere(
                        $qb->expr()->orX(
                            $qb->expr()->like('a.quickcheckqtext', ':word' . $i),
                            $qb->expr()->like('a.quickcheckqanswer', ':word' . $i)
                        )
                    );
                    $qb->setParameter('word' . $i, '%' . $words[$i] . '%');
                }
                break;
            case 'EXACT':
                $phrase = implode(" ", $words);
                $qb->where($qb->expr()->orX(
                    $qb->expr()->like('a.quickcheckqtext', ':word'),
                    $qb->expr()->like('a.quickcheckqanswer', ':word')
                    )
                );
                $qb->setParameter('word', '%' . $phrase . '%');
                break;
        }
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }
}

