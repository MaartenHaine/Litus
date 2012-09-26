<?php

namespace ShiftBundle\Repository;

use DateTime,
    CalendarBundle\Entity\Nodes\Event,
    CommonBundle\Entity\Users\Person,
    Doctrine\ORM\EntityRepository,
    ShiftBundle\Entity\Unit as UnitEntity;

/**
 * Shift
 *
 * Flight Mode
 * This file was edited by Pieter Maene while in flight from Vienna to Brussels
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Shift extends EntityRepository
{
    public function findAllActive()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                    $query->expr()->gt('s.endDate', ':now')
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllActiveByEvent(Event $event)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                    $query->expr()->andX(
                        $query->expr()->gt('s.endDate', ':now'),
                        $query->expr()->eq('s.event', ':event')
                    )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllActiveByUnit(UnitEntity $unit)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                    $query->expr()->andX(
                        $query->expr()->gt('s.endDate', ':now'),
                        $query->expr()->eq('s.unit', ':unit')
                    )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('unit', $unit)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllActiveBetweenDates($startDate, $endDate)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                    $query->expr()->andX(
                        $query->expr()->gt('s.endDate', ':now'),
                        $query->expr()->lt('s.startDate', ':end_date'),
                        $query->expr()->gt('s.endDate', ':start_date')
                    )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllActiveByPerson(Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $responsibleResultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.responsibles', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.endDate', ':now'),
                    $query->expr()->eq('r.person', ':person')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();

        $query = $this->_em->createQueryBuilder();
        $volunteerResultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.volunteers', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.endDate', ':now'),
                    $query->expr()->eq('v.person', ':person')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();

        return array_merge(
            $responsibleResultSet, $volunteerResultSet
        );
    }
}
