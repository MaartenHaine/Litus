<?php

namespace CudiBundle\Repository\Sale;

use CommonBundle\Entity\General\Bank\CashRegister,
    CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Entity\General\Organization,
    CudiBundle\Entity\Sale\Session as SessionEntity,
    DateTime,
    Doctrine\ORM\EntityRepository;

/**
 * Session
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Session extends EntityRepository
{
    public function findOneByCashRegister(CashRegister $cashRegister)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Sale\Session', 's')
            ->where($query->expr()->orX(
                    $query->expr()->eq('s.openRegister', ':register'),
                    $query->expr()->eq('s.closeRegister', ':register')
                )
            )
            ->setParameter('register', $cashRegister->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (isset($resultSet[0]))
            return $resultSet[0];

        return null;
    }

    public function getTheoreticalRevenue(SessionEntity $session, Organization $organization = null)
    {
        if ($organization !== null) {
            $session->setEntityManager($this->getEntityManager());

            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('p.id')
                ->from('CommonBundle\Entity\User\Person\Organization\AcademicYearMap', 'm')
                ->innerJoin('m.academic', 'p')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('m.organization', ':organization'),
                        $query->expr()->eq('m.academicYear', ':academicYear')
                    )
                )
                ->setParameter('organization', $organization)
                ->setParameter('academicYear', $session->getAcademicYear())
                ->getQuery()
                ->getResult();

            $ids = array(0);
            foreach($resultSet as $item) {
                $ids[] = $item['id'];
            }

            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(s.price)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.queueItem', 'q')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->in('q.person', $ids),
                        $query->expr()->eq('s.session', ':session')
                    )
                )
                ->setParameter('session', $session->getId())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        } else {
            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(s.price)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->where(
                    $query->expr()->eq('s.session', ':session')
                )
                ->setParameter('session', $session->getId())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        }
    }

    public function getTheoreticalRevenueByAcademicYear(AcademicYear $academicYear, Organization $organization = null)
    {
        if ($organization !== null) {
            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('p.id')
                ->from('CommonBundle\Entity\User\Person\Organization\AcademicYearMap', 'm')
                ->innerJoin('m.academic', 'p')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('m.organization', ':organization'),
                        $query->expr()->eq('m.academicYear', ':academicYear')
                    )
                )
                ->setParameter('organization', $organization)
                ->setParameter('academicYear', $academicYear)
                ->getQuery()
                ->getResult();

            $ids = array(0);
            foreach($resultSet as $item) {
                $ids[] = $item['id'];
            }

            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(s.price)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.session', 'e')
                ->innerJoin('s.queueItem', 'q')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->in('q.person', $ids),
                        $query->expr()->gt('e.openDate', ':start'),
                        $query->expr()->lt('e.openDate', ':end')
                    )
                )
                ->setParameter('start', $academicYear->getStartDate())
                ->setParameter('end', $academicYear->getEndDate())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        } else {
            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(s.price)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.session', 'e')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->gt('e.openDate', ':start'),
                        $query->expr()->lt('e.openDate', ':end')
                    )
                )
                ->setParameter('start', $academicYear->getStartDate())
                ->setParameter('end', $academicYear->getEndDate())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        }
    }

    public function getPurchasedAmountBySession(SessionEntity $session, Organization $organization = null)
    {
        if ($organization !== null) {
            $session->setEntityManager($this->getEntityManager());

            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('p.id')
                ->from('CommonBundle\Entity\User\Person\Organization\AcademicYearMap', 'm')
                ->innerJoin('m.academic', 'p')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('m.organization', ':organization'),
                        $query->expr()->eq('m.academicYear', ':academicYear')
                    )
                )
                ->setParameter('organization', $organization)
                ->setParameter('academicYear', $session->getAcademicYear())
                ->getQuery()
                ->getResult();

            $ids = array(0);
            foreach($resultSet as $item) {
                $ids[] = $item['id'];
            }

            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(a.purchasePrice * s.number)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.article', 'a')
                ->innerJoin('s.queueItem', 'q')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->in('q.person', $ids),
                        $query->expr()->eq('s.session', ':session')
                    )
                )
                ->setParameter('session', $session->getId())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        } else {
            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(a.purchasePrice * s.number)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.article', 'a')
                ->where(
                    $query->expr()->eq('s.session', ':session')
                )
                ->setParameter('session', $session->getId())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        }
    }

    public function getPurchasedAmountByAcademicYear(AcademicYear $academicYear, Organization $organization = null)
    {
        if ($organization !== null) {
            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('p.id')
                ->from('CommonBundle\Entity\User\Person\Organization\AcademicYearMap', 'm')
                ->innerJoin('m.academic', 'p')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('m.organization', ':organization'),
                        $query->expr()->eq('m.academicYear', ':academicYear')
                    )
                )
                ->setParameter('organization', $organization)
                ->setParameter('academicYear', $academicYear)
                ->getQuery()
                ->getResult();

            $ids = array(0);
            foreach($resultSet as $item) {
                $ids[] = $item['id'];
            }

            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(a.purchasePrice * s.number)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.session', 'e')
                ->innerJoin('s.article', 'a')
                ->innerJoin('s.queueItem', 'q')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->in('q.person', $ids),
                        $query->expr()->gt('e.openDate', ':start'),
                        $query->expr()->lt('e.openDate', ':end')
                    )
                )
                ->setParameter('start', $academicYear->getStartDate())
                ->setParameter('end', $academicYear->getEndDate())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        } else {
            $query = $this->_em->createQueryBuilder();
            $resultSet = $query->select('SUM(a.purchasePrice * s.number)')
                ->from('CudiBundle\Entity\Sale\SaleItem', 's')
                ->innerJoin('s.session', 'e')
                ->innerJoin('s.article', 'a')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->gt('e.openDate', ':start'),
                        $query->expr()->lt('e.openDate', ':end')
                    )
                )
                ->setParameter('start', $academicYear->getStartDate())
                ->setParameter('end', $academicYear->getEndDate())
                ->getQuery()
                ->getSingleScalarResult();

            if (null === $resultSet)
                $resultSet = 0;

            return $resultSet;
        }
    }

    public function getLast()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Sale\Session', 's')
            ->setMaxResults(1)
            ->orderBy('s.openDate', 'DESC')
            ->getQuery()
            ->getResult();

        if (isset($resultSet[0]))
            return $resultSet[0];

        return null;
    }

    public function findOnebyDate(DateTime $date)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Sale\Session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->lte('s.openDate', ':now'),
                    $query->expr()->gte('s.closeDate', ':now')
                )
            )
            ->setMaxResults(1)
            ->setParameter('now', $date)
            ->getQuery()
            ->getResult();

        if (isset($resultSet[0]))
            return $resultSet[0];

        return null;
    }

    public function findOpen()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Sale\Session', 's')
            ->where(
                $query->expr()->isNull('s.closeDate')
            )
            ->orderBy('s.openDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByAcademicYear(AcademicYear $academicYear)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Sale\Session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.openDate', ':start'),
                    $query->expr()->lt('s.openDate', ':end')
                )
            )
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->getQuery()
            ->getResult();

        return $resultSet;
    }
}
