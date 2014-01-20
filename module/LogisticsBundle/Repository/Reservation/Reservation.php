<?php

namespace LogisticsBundle\Repository\Reservation;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    DateTime,
    LogisticsBundle\Entity\Reservation\ReservableResource as ReservableResourceEntity;

/**
 * Reservation
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Reservation extends EntityRepository
{
    public function findAllQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\Reservation', 'r')
            ->getQuery();

        return $resultSet;
    }

    public function findAllConflictingQuery(DateTime $startDate, DateTime $endDate, ReservableResourceEntity $resource)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\Reservation', 'r')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('r.resource', ':resource'),
                    $query->expr()->lt('r.startDate', ':end_date'),
                    $query->expr()->gt('r.endDate', ':start_date')
                )
            )
            ->setParameter('resource', $resource)
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery();

        return $resultSet;
    }

    /**
     * Finds all resources conflicting with the given start and end date for the given resource. Additionally, one id can be ignored to avoid conflicts with
     * the resource itself.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param \LogisticsBundle\Entity\Reservation\ReservableResource $resource
     * @param int $ignoreId
     * @return array
     */
    public function findAllConflictingIgnoringIdQuery(DateTime $startDate, DateTime $endDate, ReservableResourceEntity $resource, $ignoreId) {

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\Reservation', 'r')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('r.resource', ':resource'),
                    $query->expr()->lt('r.startDate', ':end_date'),
                    $query->expr()->gt('r.endDate', ':start_date'),
                    $query->expr()->neq('r.id', ':id')
                )
            )
            ->setParameter('resource', $resource)
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('id', $ignoreId)
            ->getQuery();

        return $resultSet;
    }

}
