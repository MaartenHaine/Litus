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

namespace SyllabusBundle\Repository\Group;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    CommonBundle\Entity\General\AcademicYear,
    SyllabusBundle\Entity\Group as GroupEntity,
    SyllabusBundle\Entity\Study as StudyEntity;

/**
 * StudyMap
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StudyMap extends EntityRepository
{
    public function findAllByGroupAndAcademicYearQuery(GroupEntity $group, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('m')
            ->from('SyllabusBundle\Entity\Group\StudyMap', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('m.group', ':group'),
                    $query->expr()->eq('m.academicYear', ':academicYear')
                )
            )
            ->setParameter('group', $group)
            ->setParameter('academicYear', $academicYear)
            ->getQuery();

        return $resultSet;
    }

    public function findOneByStudyGroupAndAcademicYear(StudyEntity $study, GroupEntity $group, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('m')
            ->from('SyllabusBundle\Entity\Group\StudyMap', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('m.group', ':group'),
                    $query->expr()->eq('m.academicYear', ':academicYear'),
                    $query->expr()->eq('m.study', ':study')
                )
            )
            ->setParameter('group', $group)
            ->setParameter('academicYear', $academicYear)
            ->setParameter('study', $study)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
