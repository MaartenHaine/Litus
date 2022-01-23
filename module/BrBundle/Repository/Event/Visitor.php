<?php

namespace BrBundle\Repository\Event;

use DateTime;
use BrBundle\Entity\Event;

/**
 * Visitor
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Visitor extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    public function findByEventAndQr(Event $event, string $qr)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('v')
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.qrCode', ':qr'),
                    $query->expr()->eq('v.event', ':event'),
                )
            )
            ->setParameter('qr', $qr)
            ->setParameter('event', $event->getId())
            ->getQuery()
            ->getResult();
    }

    public function findSortedByEvent(Event $event)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('v')
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.event', ':event'),
                )
            )
            ->orderBy('v.entryTimestamp')
            ->setParameter('event', $event->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByEventAndQrAndExitNull(Event $event,string $qr)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('v')
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.qrCode', ':qr'),
                    $query->expr()->eq('v.event', ':event'),
                    $query->expr()->isNull('v.exitTimestamp')
                )
            )
            ->setParameter('qr', $qr)
            ->setParameter('event', $event->getId())
            ->getQuery()
            ->getResult();
    }


    public function countBetweenByEvent(Event $event, DateTime $begin, DateTime $end)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select($query->expr()->countDistinct('v.qrCode'))
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.event', ':event'),
                    $query->expr()->orX(
                        $query->expr()->gte('v.exitTimestamp', ':begin'),
                        $query->expr()->isNull('v.exitTimestamp')
                    ),
                    $query->expr()->lte('v.entryTimestamp', ':end'),
                )
            )
            ->setParameter('event', $event)
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function countAtTimeByEvent(Event $event, DateTime $time)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select($query->expr()->countDistinct('v.qrCode'))
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.event', ':event'),
                    $query->expr()->orX(
                        $query->expr()->gte('v.exitTimestamp', ':time'),
                        $query->expr()->isNull('v.exitTimestamp')
                    ),
                    $query->expr()->lte('v.entryTimestamp', ':time'),
                )
            )
            ->setParameter('event', $event)
            ->setParameter('time', $time)
            ->getQuery()
            ->getResult();
    }

    public function countUniqueByEvent(Event $event)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select($query->expr()->countDistinct('v.qrCode'))
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.event', ':event'),
                )
            )
            ->setParameter('event', $event->getId())
            ->getQuery()
            ->getResult();
    }

    public function findCurrentVisitors(Event $event)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('v')
            ->from('BrBundle\Entity\Event\Visitor', 'v')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.event', ':event'),
                    $query->expr()->isNull('v.exitTimestamp')
                )
            )
            ->setParameter('event', $event->getId())
            ->getQuery()
            ->getResult();
    }
    
}
