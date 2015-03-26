<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Koen Certyn <koen.certyn@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Dario Incalza <dario.incalza@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Lars Vierbergen <lars.vierbergen@litus.cc>
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace ApiBundle\Repository;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    DateTime;

/**
 * Key
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Key extends EntityRepository
{
    public function findAllActiveQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('k')
            ->from('ApiBundle\Entity\Key', 'k')
            ->where(
                $query->expr()->gt('k.expirationTime', ':now')
            )
            ->setParameter('now', new DateTime())
            ->getQuery();

        return $resultSet;
    }

    public function findOneActiveByCode($code)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('k')
            ->from('ApiBundle\Entity\Key', 'k')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('k.code', ':code'),
                    $query->expr()->gt('k.expirationTime', ':now')
                )
            )
            ->setParameter('code', $code)
            ->setParameter('now', new DateTime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
