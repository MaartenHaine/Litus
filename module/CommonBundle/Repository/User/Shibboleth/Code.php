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

namespace CommonBundle\Repository\User\Shibboleth;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    DateTime;

/**
 * Code
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Code extends EntityRepository
{
    public function findLastByUniversityIdentification($universityIdentification)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('CommonBundle\Entity\User\Shibboleth\Code', 'c')
            ->where(
                $query->expr()->eq('c.universityIdentification', ':universityIdentification')
            )
            ->orderBy('c.creationTime', 'DESC')
            ->setParameter('universityIdentification', $universityIdentification)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findAllExpiredQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('CommonBundle\Entity\User\Shibboleth\Code', 'c')
            ->where(
                $query->expr()->lt('c.expirationTime', ':expirationTime')
            )
            ->setParameter('expirationTime', new DateTime('now'))
            ->getQuery();

        return $resultSet;
    }
}
