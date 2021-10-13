<?php

namespace LogisticsBundle\Repository\Reservation;

/**
 * Resource
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Resource extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    public function findOneByName($name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\Resource', 'r')
            ->where(
                $query->expr()->eq('r.name', ':name')
            )
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\Resource', 'r')
            ->getQuery();
    }
}
