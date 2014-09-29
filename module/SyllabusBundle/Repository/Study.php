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

namespace SyllabusBundle\Repository;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    CommonBundle\Entity\General\AcademicYear,
    SyllabusBundle\Entity\Study as StudyEntity;

/**
 * Study
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Study extends EntityRepository
{
    public function findAllQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('SyllabusBundle\Entity\Study', 's')
            ->getQuery();

        return $resultSet;
    }

    public function findOneByTitlePhaseAndLanguage($title, $phase, $language)
    {
        if (! is_numeric($phase)) {
            return null;
        }

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('SyllabusBundle\Entity\Study', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('s.title', ':title'),
                    $query->expr()->eq('s.phase', ':phase'),
                    $query->expr()->eq('s.language', ':language')
                )
            )
            ->setParameter('title', $title)
            ->setParameter('phase', $phase)
            ->setParameter('language', $language)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findOneByTitlePhaseLanguageAndParent($title, $phase, $language, StudyEntity $parent)
    {
        if (! is_numeric($phase)) {
            return null;
        }

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('SyllabusBundle\Entity\Study', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('s.title', ':title'),
                    $query->expr()->eq('s.phase', ':phase'),
                    $query->expr()->eq('s.language', ':language'),
                    ($parent ? $query->expr()->eq('s.parent', $parent->getId()) : $query->expr()->isNull('s.parent'))
                )
            )
            ->setParameter('title', $title)
            ->setParameter('phase', $phase)
            ->setParameter('language', $language)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findAllParentsByAcademicYearQuery(AcademicYear $academicYear)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('m')
            ->from('SyllabusBundle\Entity\AcademicYearMap', 'm')
            ->where(
                $query->expr()->eq('m.academicYear', ':academicYear')
            )
            ->setParameter('academicYear', $academicYear->getId())
            ->getQuery()
            ->getResult();

        $ids = array(0);
        foreach ($resultSet as $result) {
            $ids[$result->getStudy()->getId()] = $result->getStudy()->getId();
            foreach ($result->getStudy()->getParents() as $parent) {
                $ids[$parent->getId()] = $parent->getId();
            }
        }

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('SyllabusBundle\Entity\Study', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('s.id', $ids),
                    $query->expr()->isNull('s.parent')
                )
            )
            ->orderBy('s.title', 'ASC')
            ->addOrderBy('s.phase', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByTitleAndAcademicYearTypeAhead($title, AcademicYear $academicYear)
    {
        if ('' == $title) {
            return array();
        }

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('m')
            ->from('SyllabusBundle\Entity\AcademicYearMap', 'm')
            ->where(
                $query->expr()->eq('m.academicYear', ':academicYear')
            )
            ->setParameter('academicYear', $academicYear->getId())
            ->getQuery()
            ->getResult();

        $result = array();

        $title = strtolower($title);

        foreach ($resultSet as $mapping) {
            if (strpos(strtolower($mapping->getStudy()->getFullTitle()), $title) !== false) {
                $result[] = $mapping->getStudy();
            }
        }

        return $result;
    }

    public function findOneByKulId($kulId)
    {
        if (! is_numeric($kulId)) {
            return null;
        }

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('SyllabusBundle\Entity\Study', 's')
            ->where(
                $query->expr()->eq('s.kulId', ':kulId')
            )
            ->setParameter('kulId', $kulId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
