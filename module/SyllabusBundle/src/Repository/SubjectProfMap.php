<?php

namespace SyllabusBundle\Repository;

use Doctrine\ORM\EntityRepository;

use CommonBundle\Entity\Users\People\Academic,
    SyllabusBundle\Entity\Subject as SubjectEntity;

/**
 * SubjectProfMap
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SubjectProfMap extends EntityRepository
{
    public function findOneBySubjectAndProf(SubjectEntity $subject, Academic $prof)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('m')
        	->from('SyllabusBundle\Entity\SubjectProfMap', 'm')
        	->where(
        	    $query->expr()->andX(
        	        $query->expr()->eq('m.subject', ':subject'),
        	        $query->expr()->eq('m.prof', ':prof')
        	    )
        	)
        	->setParameter('subject', $subject->getId())
        	->setParameter('prof', $prof->getId())
        	->setMaxResults(1)
        	->getQuery()
        	->getResult();
        
        if (isset($resultSet[0]))
        	return $resultSet[0];
        
        return null;
    }
    
    public function findAllBySubject(SubjectEntity $subject)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('m')
        	->from('SyllabusBundle\Entity\SubjectProfMap', 'm')
        	->where($query->expr()->in('m.subject', ':subject'))
        	->setParameter('subject', $subject->getId())
        	->getQuery()
        	->getResult();
        	
        return $resultSet;
    }
}