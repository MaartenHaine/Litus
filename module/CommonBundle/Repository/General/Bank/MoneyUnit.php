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
 * @author Mathijs Cuppens <mathijs.cuppens@litus.cc>
 * @author Floris Kint <floris.kint@vtk.be>
 *
 * @license http://litus.cc/LICENSE
 */

namespace CommonBundle\Repository\General\Bank;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * MoneyUnit
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MoneyUnit extends EntityRepository
{
    /**
     * @param  int                                             $unit
     * @return CommonBundle\Entity\General\Bank\MoneyUnit|null
     */
    public function findOneByUnit($unit)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('u')
            ->from('CommonBundle\Entity\General\Bank\MoneyUnit', 'u')
            ->where(
                $query->expr()->eq('u.unit', ':unit')
            )
            ->setParameter('unit', $unit * 100)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
