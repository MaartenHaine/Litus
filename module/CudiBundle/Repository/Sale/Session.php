<?php

namespace CudiBundle\Repository\Sale;

use CommonBundle\Entity\General\Bank\CashRegister,
    CommonBundle\Entity\General\AcademicYear,
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

    public function getTheoreticalRevenue(SessionEntity $session)
    {
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
            ->where($query->expr()->orX(
                    $query->expr()->gt('s.openDate', ':start'),
                    $query->expr()->lt('s.openDate', ':end')
                )
            )
            ->setParameter('start', $academicYear->getUniversityStartDate())
            ->setParameter('end', $academicYear->getUniversityEndDate())
            ->getQuery()
            ->getResult();

        return $resultSet;
    }
}
