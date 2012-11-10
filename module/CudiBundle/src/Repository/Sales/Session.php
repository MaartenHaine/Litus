<?php

namespace CudiBundle\Repository\Sales;

use CommonBundle\Entity\General\Bank\CashRegister,
    CudiBundle\Entity\Sales\Session as SessionEntity,
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
            ->from('CudiBundle\Entity\Sales\Session', 's')
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
            ->from('CudiBundle\Entity\Sales\SaleItem', 's')
            ->where(
                $query->expr()->eq('s.session', ':session')
            )
            ->setParameter('session', $session->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if (null === $resultSet)
            $resultSet = 0;

        $query = $this->_em->createQueryBuilder();
        $members = $query->select('COUNT(r.id)')
            ->from('SecretaryBundle\Entity\Registration', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gte('r.payedTimestamp', ':startTime'),
                    $query->expr()->lte('r.payedTimestamp', ':endTime'),
                    $query->expr()->eq('r.payed', 'true')
                )
            )
            ->setParameter('startTime', $session->getOpenDate())
            ->setParameter('endTime', $session->getCloseDate())
            ->getQuery()
            ->getSingleScalarResult();

        $resultSet += $members * $this->_em
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('secretary.membership_price');

        return $resultSet;
    }

    public function getLast()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Sales\Session', 's')
            ->setMaxResults(1)
            ->orderBy('s.openDate', 'DESC')
            ->getQuery()
            ->getResult();

        if (isset($resultSet[0]))
            return $resultSet[0];

        return null;
    }
}
