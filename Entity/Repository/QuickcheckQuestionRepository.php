<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;

class QuickcheckQuestionRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuickcheckQuestionEntity::class);
    }

    /**
     * @param array $words
     * @param string $searchType
     * @param bool $full
     * @return array
     */
    public function getSearchResults(array $words, string $searchType, bool $full=false) :array {
        $qb = $this->_em->createQueryBuilder();
        if($full){
            $qb->select('a');
        } else {
            $qb->select('a.id', 'a.quickcheckqtype', 'a.quickcheckqtext');
        }

        $qb->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'a');
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
        $results = $query->getArrayResult();
        return $results;
    }
}

