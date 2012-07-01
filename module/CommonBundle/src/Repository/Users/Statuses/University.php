<?php

namespace CommonBundle\Repository\Users\Statuses;

use CommonBundle\Entity\General\AcademicYear,
    Doctrine\ORM\EntityRepository;

/**
 * University
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class University extends EntityRepository
{
    public function findAllByStatus($status, AcademicYear $academicYear)
    {
    	$query = $this->_em->createQueryBuilder();
    	$resultSet = $query->select('s')
    		->from('CommonBundle\Entity\Users\Statuses\University', 's')
    		->where(
    		    $query->expr()->andX(
    		        $query->expr()->eq('s.status', ':status'),
    		        $query->expr()->eq('s.academicYear', ':academicYear')
    		    )
    		)
    		->setParameter('status', $status)
    		->setParameter('academicYear', $academicYear->getId())
    		->getQuery()
    		->getResult();
    	
    	return $resultSet;
    }
}