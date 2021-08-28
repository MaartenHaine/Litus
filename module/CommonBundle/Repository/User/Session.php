<?php

namespace CommonBundle\Repository\User;

use CommonBundle\Entity\User\Person as PersonEntity;
use DateTime;

/**
 * Session
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Session extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllExpiredQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('CommonBundle\Entity\User\Session', 's')
            ->where(
                $query->expr()->orX(
                    $query->expr()->lt('s.expirationTime', ':expirationTime'),
                    $query->expr()->eq('s.active', 'false')
                )
            )
            ->setParameter('expirationTime', new DateTime('now'))
            ->getQuery();
    }

    /**
     * @param  PersonEntity $person
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveByPersonQuery(PersonEntity $person)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('s')
            ->from('CommonBundle\Entity\User\Session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('s.person', ':person'),
                    $query->expr()->andX(
                        $query->expr()->gt('s.expirationTime', ':expirationTime'),
                        $query->expr()->eq('s.active', 'true')
                    )
                )
            )
            ->setParameter('person', $person)
            ->setParameter('expirationTime', new DateTime('now'))
            ->getQuery();
    }
}
