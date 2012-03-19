<?php

namespace CommonBundle\Repository\Users;

use Doctrine\ORM\EntityRepository;

/**
 * Person
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Person extends EntityRepository
{
	public function findAllByRole($role)
	{
		$resultSet = $this->_em
		    ->createQuery('SELECT p FROM CommonBundle\Entity\Users\Person p JOIN p.roles r WHERE r.name = \'' . $role . '\'')
		    ->getResult();
		    
		return $resultSet;
	}
	
	public function findAllByName($name)
	{
		$query = $this->_em->createQueryBuilder();
		$resultSet = $query->select('p')
			->from('CommonBundle\Entity\Users\Person', 'p')
			->where(
				$query->expr()->orX(
					$query->expr()->like(
						$query->expr()->concat(
							$query->expr()->lower($query->expr()->concat('p.firstName', "' '")),
							$query->expr()->lower('p.lastName')
						),
						':name'
					),
					$query->expr()->like(
						$query->expr()->concat(
							$query->expr()->lower($query->expr()->concat('p.lastName', "' '")),
							$query->expr()->lower('p.firstName')
						),
						':name'
					)
				))
			->setParameter('name', '%' . strtolower($name) . '%')
			->getQuery()
			->getResult();
		
		return $resultSet;
	}
	
	public function findAllByUsername($username)
	{
		$query = $this->_em->createQueryBuilder();
		$resultSet = $query->select('p')
			->from('CommonBundle\Entity\Users\Person', 'p')
			->where(
				$query->expr()->like('p.username', ':username')
			)
			->setParameter('username', '%' . strtolower($username) . '%')
			->getQuery()
			->getResult();
		
		return $resultSet;
	}
	
	public function findOneByUsername($username)
    {
    	$query = $this->_em->createQueryBuilder();
		$resultSet = $query->select('p')
			->from('CommonBundle\Entity\Users\Person', 'p')
			->where($query->expr()->eq('p.username', ':username'))
			->setParameter('username', $username)
			->setMaxResults(1)
			->getQuery()
			->getResult();
		
		if (isset($resultSet[0]))
            return $resultSet[0];
        
        $barcode = $this->_em
            ->getRepository('CommonBundle\Entity\Users\Barcode')
            ->findOneByBarcode($username);
        
        if ($barcode)
            return $barcode->getPerson();
        
        return null;
    }
}