<?php

namespace SyllabusBundle\Repository\Subject;

use SyllabusBundle\Entity\Subject\Comment as CommentEntity,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Reply
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Reply extends EntityRepository
{
    public function findLast($nb = 10)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('SyllabusBundle\Entity\Subject\Reply', 'r')
            ->innerJoin('r.comment', 'c')
            ->where(
                $query->expr()->isNull('c.readBy')
            )
            ->orderBy('r.date', 'DESC')
            ->setMaxResults($nb)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByComment(CommentEntity $comment)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('SyllabusBundle\Entity\Subject\Reply', 'r')
            ->where(
                $query->expr()->eq('r.comment', ':comment')
            )
            ->orderBy('r.date', 'ASC')
            ->setParameter('comment', $comment)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findLastByComment(CommentEntity $comment)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('SyllabusBundle\Entity\Subject\Reply', 'r')
            ->where(
                $query->expr()->eq('r.comment', ':comment')
            )
            ->orderBy('r.date', 'DESC')
            ->setParameter('comment', $comment)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
