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

namespace BrBundle\Repository;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Company
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Company extends EntityRepository
{
    public function findAllQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('BrBundle\Entity\Company', 'c')
            ->where(
                $query->expr()->eq('c.active', 'true')
            )
            ->orderBy('c.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByNameQuery($name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('BrBundle\Entity\Company', 'c')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->like($query->expr()->lower('c.name'), ':name')
                )
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->orderBy('c.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }
}
