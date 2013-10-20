<?php

namespace LogisticsBundle\Repository\Lease;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Item
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Item extends EntityRepository
{
    public function searchByName($name)
    {
        $query = $this->createQueryBuilder('i');

        return $query->select()
            ->where(
                $query->expr()->like(
                    $query->expr()->lower('i.name'),
                    ':name'
                )
            )
            ->setParameter('name', '%'.strtolower($name).'%')
            ->setMaxResults(20)
            ->getQuery()
            ->execute();
    }
}
