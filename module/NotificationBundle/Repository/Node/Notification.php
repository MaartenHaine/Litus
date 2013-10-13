<?php

namespace NotificationBundle\Repository\Node;

use DateTime,
    CommonBundle\Component\Util\EntityRepository;

/**
 * Notification
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Notification extends EntityRepository
{
    public function findAllQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('n')
            ->from('NotificationBundle\Entity\Node\Notification', 'n')
            ->orderBy('n.creationTime', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveQuery()
    {
        $now = new DateTime();

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('n')
            ->from('NotificationBundle\Entity\Node\Notification', 'n')
            ->where(
                $query->expr()->andx(
                    $query->expr()->lte('n.startDate', ':now'),
                    $query->expr()->gte('n.endDate', ':now'),
                    $query->expr()->eq('n.active', 'true')
                )
            )
            ->setParameter('now', $now)
            ->orderBy('n.creationTime', 'DESC')
            ->getQuery();

        return $resultSet;
    }
}
