<?php

namespace NewsBundle\Repository\Nodes;

use Doctrine\ORM\EntityRepository;

/**
 * News
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class News extends EntityRepository
{
    public function findAll($nbResults = 3)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('n')
            ->from('NewsBundle\Entity\Nodes\News', 'n')
            ->orderBy('n.creationTime', 'DESC')
            ->setMaxResults($nbResults)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }
}
