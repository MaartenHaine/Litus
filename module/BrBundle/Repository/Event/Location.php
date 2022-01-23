<?php

namespace BrBundle\Repository\Event;

use BrBundle\Entity\Event;

/**
 * Location
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Location extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    public function findAllByEventQuery(Event $event)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('l')
            ->from('BrBundle\Entity\Event\Location', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.event', ':event')
                )
            )
            ->orderBy('l.number')
            ->setParameter('event', $event->getId())
            ->getQuery();
    }
    
}
