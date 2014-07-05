<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Koen Certyn <koen.certyn@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Dario Incalza <dario.incalza@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Lars Vierbergen <lars.vierbergen@litus.cc>
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace LogisticsBundle\Repository\Reservation;

use CommonBundle\Entity\User\Person,
    CommonBundle\Component\Doctrine\ORM\EntityRepository,
    DateTime,
    LogisticsBundle\Entity\Reservation\ReservableResource as ReservableResourceEntity;

/**
 * PianoReservation
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PianoReservation extends EntityRepository
{
    public function findAllActiveQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $query->expr()->gte('r.endDate', ':start')
            )
            ->setParameter('start', new DateTime())
            ->orderBy('r.startDate', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllOldQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $query->expr()->lt('r.endDate', ':end')
            )
            ->setParameter('end', new DateTime())
            ->orderBy('r.startDate', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByDatesQuery(DateTime $start, DateTime $end)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $query->expr()->orx(
                    $query->expr()->andx(
                        $query->expr()->gte('r.startDate', ':start'),
                        $query->expr()->lte('r.startDate', ':end')
                    ),
                    $query->expr()->andx(
                        $query->expr()->gte('r.endDate', ':start'),
                        $query->expr()->lte('r.endDate', ':end')
                    )
                )
            )
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery();

        return $resultSet;
    }

    public function findAllConfirmedByDatesAndPersonQuery(DateTime $start, DateTime $end, Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orx(
                        $query->expr()->andx(
                            $query->expr()->gte('r.startDate', ':start'),
                            $query->expr()->lte('r.startDate', ':end')
                        ),
                        $query->expr()->andx(
                            $query->expr()->gte('r.endDate', ':start'),
                            $query->expr()->lte('r.endDate', ':end')
                        )
                    ),
                    $query->expr()->eq('r.player', ':person'),
                    $query->expr()->eq('r.confirmed', 'true')
                )
            )
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('person', $person)
            ->getQuery();

        return $resultSet;
    }

    public function findAllByDatesAndPersonQuery(DateTime $start, DateTime $end, Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orx(
                        $query->expr()->andx(
                            $query->expr()->gte('r.startDate', ':start'),
                            $query->expr()->lte('r.startDate', ':end')
                        ),
                        $query->expr()->andx(
                            $query->expr()->gte('r.endDate', ':start'),
                            $query->expr()->lte('r.endDate', ':end')
                        )
                    ),
                    $query->expr()->eq('r.player', ':person')
                )
            )
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('person', $person)
            ->getQuery();

        return $resultSet;
    }

    public function isTimeInExistingReservation(DateTime $date, $isStart)
    {
        $query = $this->_em->createQueryBuilder();

        if ($isStart) {
            $where = $query->expr()->andX(
                $query->expr()->lte('r.startDate', ':date'),
                $query->expr()->gt('r.endDate', ':date'),
                $query->expr()->eq('r.confirmed', 'true')
            );
        } else {
            $where = $query->expr()->andX(
                $query->expr()->lt('r.startDate', ':date'),
                $query->expr()->gte('r.endDate', ':date'),
                $query->expr()->eq('r.confirmed', 'true')
            );
        }

        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $where
            )
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return isset($resultSet[0]);
    }

    /**
     * Finds all resources conflicting with the given start and end date for the given resource. Additionally, one id can be ignored to avoid conflicts with
     * the resource itself.
     *
     * @param  DateTime                 $startDate
     * @param  DateTime                 $endDate
     * @param  ReservableResourceEntity $resource
     * @param  int                      $ignoreId
     * @return \Doctrine\ORM\Query
     */
    public function findAllConflictingIgnoringIdQuery(DateTime $startDate, DateTime $endDate, ReservableResourceEntity $resource, $ignoreId)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('LogisticsBundle\Entity\Reservation\PianoReservation', 'r')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('r.resource', ':resource'),
                    $query->expr()->lt('r.startDate', ':end_date'),
                    $query->expr()->gt('r.endDate', ':start_date'),
                    $query->expr()->neq('r.id', ':id'),
                    $query->expr()->eq('r.confirmed', 'true')
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
