<?php

namespace CudiBundle\Repository\Stock\Period\Value;

use CudiBundle\Entity\Sale\Article,
    CudiBundle\Entity\Stock\Period,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Delta
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Delta extends EntityRepository
{
    public function findTotalByArticleAndPeriod(Article $article, Period $period)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('SUM(v.value)')
            ->from('CudiBundle\Entity\Stock\Period\Value\Delta', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.article', ':article'),
                    $query->expr()->eq('v.period', ':period')
                )
            )
            ->setParameter('article', $article->getId())
            ->setParameter('period', $period->getId())
            ->getQuery()
            ->getSingleScalarResult();

       return $resultSet;
    }

    public function findAllByArticleAndPeriodQuery(Article $article, Period $period)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v')
            ->from('CudiBundle\Entity\Stock\Period\Value\Delta', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.article', ':article'),
                    $query->expr()->eq('v.period', ':period')
                )
            )
            ->setParameter('article', $article->getId())
            ->setParameter('period', $period->getId())
            ->orderBy('v.timestamp', 'DESC')
            ->getQuery();

       return $resultSet;
    }
}
