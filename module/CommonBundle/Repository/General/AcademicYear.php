<?php

namespace CommonBundle\Repository\General;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * AcademicYear
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AcademicYear extends EntityRepository
{
    public function findOneById($id) {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('y')
            ->from('CommonBundle\Entity\General\AcademicYear', 'y')
            ->where(
                $query->expr()->eq('y.id', ':id')
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findAllQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('y')
            ->from('CommonBundle\Entity\General\AcademicYear', 'y')
            ->orderBy('y.universityStart', 'DESC')
            ->getQuery();

        return $resultSet;
    }
}
