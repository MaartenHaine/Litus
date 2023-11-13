<?php

namespace ShopBundle\Repository\Reservation;

use CommonBundle\Entity\User\Person;
use DateTime;

/**
 * Ban
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Ban extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     *
     * Returns bans that are currently active or planned in the future.
     */
    public function findActiveQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('b')
            ->from('ShopBundle\Entity\Reservation\Ban', 'b')
            ->where(
                $query->expr()->orX(
                    $query->expr()->isNull('b.endTimestamp'),
                    $query->expr()->gte('b.endTimestamp', ':now')
                )
            )
            ->setParameter('now', new DateTime())
            ->getQuery();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findOldQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('b')
            ->from('ShopBundle\Entity\Reservation\Ban', 'b')
            ->where(
                $query->expr()->andX(
                    $query->expr()->isNotNull('b.endTimestamp'),
                    $query->expr()->lt('b.endTimestamp', ':now')
                )
            )
            ->setParameter('now', new DateTime())
            ->getQuery();
    }

    /**
     * @param Person $person
     * @return \Doctrine\ORM\Query
     */
    public function findAllByPersonQuery(Person $person)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('count(b.id)')
            ->from('ShopBundle\Entity\Reservation\Ban', 'b')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('b.person', ':person'),
                )
            )
            ->setParameter(':person', $person->getId())
            ->getQuery();
    }

    /**
     * Returns the bans that are currently active for $person, in ascending order.
     *
     * @param Person $person
     * @return \Doctrine\ORM\Query
     */
    public function findActiveByPersonQuery(Person $person)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder->select('b')
            ->from('ShopBundle\Entity\Reservation\Ban', 'b')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('b.person', ':person'),
                    $queryBuilder->expr()->lte('b.startTimestamp', ':now'),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->isNull('b.endTimestamp'),
                        $queryBuilder->expr()->gte('b.endTimestamp', ':now')
                    )
                )
            )
            ->setParameter('person', $person)
            ->setParameter('now', new DateTime())
            ->orderBy('b.endTimestamp', 'ASC')
            ->getQuery();

        return $query;
    }

}