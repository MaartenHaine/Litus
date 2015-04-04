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

namespace ShiftBundle\Repository;

use CalendarBundle\Entity\Node\Event,
    CommonBundle\Component\Doctrine\ORM\EntityRepository,
    CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Entity\General\Organization\Unit as UnitEntity,
    CommonBundle\Entity\User\Person,
    DateTime;

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
    public function findAllActiveQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                $query->expr()->gt('s.endDate', ':now')
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByNameQuery($name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.endDate', ':now'),
                    $query->expr()->like($query->expr()->lower('s.name'), ':name')
                )
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->getQuery();

        return $resultSet;
    }

    public function findAllOldQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->where(
                $query->expr()->lt('s.endDate', ':now')
            )
            ->orderBy('s.startDate', 'ASC')
            ->setParameter('now', new DateTime())
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByEventQuery(Event $event)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
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
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByUnitQuery(UnitEntity $unit)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
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
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveBetweenDatesQuery($startDate, $endDate)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
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
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByPerson(Person $person)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
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

        $shifts = array();
        foreach ($responsibleResultSet as $result) {
            $shifts[$result->getStartDate()->format('YmdHi') . $result->getId()] = $result;
        }

        $query = $this->getEntityManager()->createQueryBuilder();
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

        foreach ($volunteerResultSet as $result) {
            $shifts[$result->getStartDate()->format('YmdHi') . $result->getId()] = $result;
        }

        ksort($shifts);

        return array_values($shifts);
    }

    public function findAllByPerson(Person $person, AcademicYear $academicYear = null)
    {
        return array_merge(
            $this->findAllByPersonAsReponsible($person, $academicYear),
            $this->findAllByPersonAsVolunteer($person, $academicYear)
        );
    }

    public function countAllByPerson(Person $person, AcademicYear $academicYear = null)
    {
        return count(
            array_merge(
                $this->findAllByPersonAsReponsible($person, $academicYear),
                $this->findAllByPersonAsVolunteer($person, $academicYear)
            )
        );
    }

    public function findAllByPersonAsReponsibleQuery(Person $person, AcademicYear $academicYear = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $query = $queryBuilder->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.responsibles', 'r');

        if (null === $academicYear) {
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

        if (null !== $academicYear) {
            $query->setParameter('academicYear', $academicYear);
        }

        return $query->getQuery();
    }

    public function findAllByPersonAsVolunteerQuery(Person $person, AcademicYear $academicYear = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $query = $queryBuilder->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.volunteers', 'v');

        if (null === $academicYear) {
            $where = $query->expr()->eq('v.person', ':person');
        } else {
            $where = $query->expr()->andX(
                $query->expr()->eq('s.academicYear', ':academicYear'),
                $query->expr()->eq('v.person', ':person')
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

        if (null !== $academicYear) {
            $query->setParameter('academicYear', $academicYear);
        }

        return $query->getQuery();
    }

    public function findOneByVolunteer($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.volunteers', 'v')
            ->where(
                $query->expr()->eq('v.id', ':id')
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findOneByResponsible($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.responsibles', 'r')
            ->where(
                $query->expr()->eq('r.id', ':id')
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findOneActiveByVolunteer($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.volunteers', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.endDate', ':now'),
                    $query->expr()->eq('v.id', ':id')
                )
            )
            ->setParameter('now', new DateTime())
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findOneActiveByResponsible($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('ShiftBundle\Entity\Shift', 's')
            ->innerJoin('s.responsibles', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.endDate', ':now'),
                    $query->expr()->eq('r.id', ':id')
                )
            )
            ->setParameter('now', new DateTime())
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
