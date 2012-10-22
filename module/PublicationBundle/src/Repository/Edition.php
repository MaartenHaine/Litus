<?php

namespace PublicationBundle\Repository;

use Doctrine\ORM\EntityRepository,
    PublicationBundle\Entity\Publication as PublicationEntity;

/**
 * Edition
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Edition extends EntityRepository
{
    public function findAllYearsByPublication(PublicationEntity $publication)
    {
        $resultSet = $this->_em
        ->createQuery('SELECT y FROM CommonBundle\Entity\General\AcademicYear y WHERE EXISTS (SELECT e FROM PublicationBundle\Entity\Edition e WHERE e.academicYear = y AND e.publication = :publication)')
        ->setParameter('publication', $publication)
        ->getResult();

        return $resultSet;
    }
}