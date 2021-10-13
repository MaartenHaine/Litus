<?php

namespace CudiBundle\Repository;

/**
 * Deal
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Deal extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('d')
            ->from('CudiBundle\Entity\Deal', 'd')
            ->innerjoin('d.retail', 'ret')
            ->innerjoin('ret.article', 'art')
            ->orderBy('art.title', 'ASC')
            ->getQuery();
    }

//    /**
//     * @param  string $title
//     * @return \Doctrine\ORM\Query
//     */
//    public function findAllByTitleQuery($title)
//    {
//        $query = $this->getEntityManager()->createQueryBuilder();
//        return $query->select('a')
//            ->from('CudiBundle\Entity\Retail', 'a')
//            ->innerjoin('a.article', 'art')
//            ->where(
//                $query->expr()->like($query->expr()->lower('art.title'), ':title')
//            )
//            ->setParameter('title', '%' . strtolower($title) . '%')
//            ->orderBy('art.title', 'ASC')
//            ->getQuery();
//    }

    /**
     * @param  string $buyer
     * @return \Doctrine\ORM\Query
     */
    public function findAllByBuyerQuery($buyer)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('d')
            ->from('CudiBundle\Entity\Deal', 'd')
            ->innerjoin('d.buyer', 'b')
            ->innerjoin('d.retail', 'r')
            ->innerjoin('r.article', 'a')
            ->where(
                $query->expr()->eq('b.id', ':buyer')
            )
            ->setParameter('buyer', $buyer)
            ->orderBy('a.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  string $retailId
     * @return \Doctrine\ORM\Query
     */
    public function findAllByRetail($retailId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('d')
            ->from('CudiBundle\Entity\Deal', 'd')
            ->innerjoin('d.retail', 'r')
            ->where(
                $query->expr()->eq('r.id', ':retailId')
            )
            ->setParameter('retailId', $retailId)
            ->getQuery()->getResult();
    }
}
