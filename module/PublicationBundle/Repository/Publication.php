<?php

namespace PublicationBundle\Repository;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Publication
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Publication extends EntityRepository
{
    public function findOneActiveById($id)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('PublicationBundle\Entity\Publication', 'p')
            ->where(
        		$query->expr()->andX(
                	$query->expr()->eq('p.id', ':id'),
                	$query->expr()->eq('p.deleted', 'false')
            	)
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

	public function findOneByTitle($title)
	{
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('PublicationBundle\Entity\Publication', 'p')
            ->where(
                	$query->expr()->eq('p.title', ':title')
            )
            ->setParameter('title', $title)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findAllActiveQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('PublicationBundle\Entity\Publication', 'p')
            ->where(
            	$query->expr()->eq('p.deleted', 'false')
            )
            ->orderBy('p.title', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveWithEditionQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $internal = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('PublicationBundle\Entity\Publication', 'p')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('p.deleted', 'false'),
                    $query->expr()->exists(
                        'SELECT e FROM PublicationBundle\Entity\Edition e WHERE e.publication = p'
                    )
                )
            )
            ->orderBy('p.title', 'ASC')
            ->getQuery();

        return $resultSet;
    }

}
