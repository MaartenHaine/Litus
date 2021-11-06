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

namespace BrBundle\Repository;

use CommonBundle\Entity\User\Person;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Match extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @param Person $student
     * @param  $company
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByStudentAndCompany(Person $student, \BrBundle\Entity\Company $company)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('m')
            ->from('BrBundle\Entity\Match', 'm')
            ->innerJoin('m.companyMatchee', 'c')
            ->innerJoin('m.studentMatchee', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('s.student', ':student'),
                    $query->expr()->eq('c.company', ':company')
                )
            )
//            ->orderBy('p.name', 'ASC')
            ->setParameter('student', $student)
            ->setParameter('company', $company)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
