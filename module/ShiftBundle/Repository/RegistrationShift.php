<?php

namespace ShiftBundle\Repository;

use CalendarBundle\Entity\Node\Event;
use CommonBundle\Entity\General\AcademicYear;
use CommonBundle\Entity\General\Organization\Unit as UnitEntity;
use CommonBundle\Entity\User\Person;
use DateTime;

/**
 * RegistrationShift
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RegistrationShift extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->where(
                $query->expr()->gt('s.endDate', ':now')
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->getQuery();
    }

    /**
     * @param  string $name
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveByNameQuery($name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.visibleDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now')
                    ),
                    $query->expr()->like($query->expr()->lower('s.name'), ':name')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->getQuery();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllOldQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->where(
                $query->expr()->lt('s.endDate', ':now')
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->getQuery();
    }

    /**
     * @param  Event $event
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveByEventQuery(Event $event)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.visibleDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now')
                    ),
                    $query->expr()->eq('s.event', ':event')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('event', $event)
            ->getQuery();
    }

    /**
     * @param  UnitEntity $unit
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveByUnitQuery(UnitEntity $unit)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.visibleDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now')
                    ),
                    $query->expr()->eq('s.unit', ':unit')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('unit', $unit)
            ->getQuery();
    }

    /**
     * @param  DateTime $startDate
     * @param  DateTime $endDate
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveBetweenDatesQuery(DateTime $startDate, DateTime $endDate)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.visibleDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now')
                    ),
                    $query->expr()->lt('s.startDate', ':end_date'),
                    $query->expr()->gt('s.endDate', ':start_date')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery();
    }

    /**
     * @param  Person            $person
     * @param  AcademicYear|null $academicYear
     * @return array
     */
    public function findAllByPerson(Person $person, AcademicYear $academicYear = null)
    {
        return array_merge(
            $this->findAllByPersonAsRegisteredQuery($person, $academicYear)->getResult()
        );
    }

    /**
     * @param  Person            $person
     * @param  AcademicYear|null $academicYear
     * @return integer
     */
    public function countAllByPerson(Person $person, AcademicYear $academicYear = null)
    {
        return count(
            $this->findAllByPersonAsRegisteredQuery($person, $academicYear)->getResult()
        );
    }

    /**
     * @param  Person            $person
     * @param  AcademicYear|null $academicYear
     * @return \Doctrine\ORM\Query
     */
    public function findAllByPersonAsRegisteredQuery(Person $person, AcademicYear $academicYear = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $query = $queryBuilder->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->innerJoin('s.registered', 'r');

        if ($academicYear === null) {
            $where = $query->expr()->eq('r.person', ':person');
        } else {
            $where = $query->expr()->andX(
                $query->expr()->eq('s.academicYear', ':academicYear'),
                $query->expr()->eq('r.person', ':person')
            );
        }

        $query->where(
            $query->expr()->andX(
                $query->expr()->lt('s.startDate', ':now'),
                $where
            )
        )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('person', $person);

        if ($academicYear !== null) {
            $query->setParameter('academicYear', $academicYear);
        }

        return $query->getQuery();
    }

    /**
     * @param  integer $id
     * @return \ShiftBundle\Entity\RegistrationShift|null
     */
    public function findOneByRegistered($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->innerJoin('s.registered', 'r')
            ->where(
                $query->expr()->eq('r.id', ':id')
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param  integer $id
     * @return \ShiftBundle\Entity\RegistrationShift|null
     */
    public function findOneActiveByRegistered($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.registered', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.visibleDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now')
                    ),
                    $query->expr()->eq('r.id', ':id')
                )
            )
            ->setParameter('now', new DateTime())
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param  Person $person
     * @return array
     */
    public function findAllActiveByPerson(Person $person)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $responsibleResultSet = $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->innerJoin('s.registered', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.visibleDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now')
                    ),
                    $query->expr()->eq('r.person', ':person')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('person', $person->getId())
            ->getQuery()
            ->getResult();

        $shifts = array();
        foreach ($responsibleResultSet as $result) {
            $shifts[$result->getStartDate()->format('YmdHi') . $result->getId()] = $result;
        }

        ksort($shifts);

        return array_values($shifts);
    }

    /**
     * @param  Person $person
     * @return array
     */
    public function findAllCurrentAndCudiTimeslotByPerson(Person $person)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $responsibleResultSet = $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->innerJoin('s.registered', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.startDate', ':now'),
                        $query->expr()->gt('s.endDate', ':now'),
                        $query->expr()->eq('s.cudiTimeslot', 'true')
                    ),
                    $query->expr()->eq('r.person', ':person')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('person', $person->getId())
            ->getQuery()
            ->getResult();

        $shifts = array();
        foreach ($responsibleResultSet as $result) {
            $shifts[$result->getStartDate()->format('YmdHi') . $result->getId()] = $result;
        }

        ksort($shifts);

        return array_values($shifts);
    }

    /**
     * @param  Person  $person
     * @param integer $marginInMinutes
     * @return array
     */
    public function findAllCurrentAndCudiTimeslotByPersonWithMargin(Person $person, int $marginInMinutes)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $responsibleResultSet = $query->select('s')
            ->from('ShiftBundle\Entity\RegistrationShift', 's')
            ->innerJoin('s.registered', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->andX(
                        $query->expr()->lt('s.startDate', ':now_plus_margin'),
                        $query->expr()->gt('s.endDate', ':now_minus_margin'),
                        $query->expr()->eq('s.cudiTimeslot', 'true')
                    ),
                    $query->expr()->eq('r.person', ':person')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now_plus_margin', (new DateTime())->modify('+' . $marginInMinutes . 'minutes'))
            ->setParameter('now_minus_margin', (new DateTime())->modify('-' . $marginInMinutes . 'minutes'))
            ->setParameter('person', $person->getId())
            ->getQuery()
            ->getResult();

        $shifts = array();
        foreach ($responsibleResultSet as $result) {
            $shifts[$result->getStartDate()->format('YmdHi') . $result->getId()] = $result;
        }

        ksort($shifts);

        return array_values($shifts);
    }
}
