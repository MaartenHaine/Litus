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

namespace BrBundle\Repository\Company\Request;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * RequestInternship
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RequestInternship extends EntityRepository
{
    public function findNewRequests()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('BrBundle\Entity\Company\Request\RequestInternship', 'r')
            ->where(
                $query->expr()->eq('r.handled', 'FALSE')
            )
            ->getQuery()
            ->getResult();

        return $resultSet;
    }
}
