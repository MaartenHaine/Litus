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

namespace SportBundle\Repository;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    CommonBundle\Entity\General\AcademicYear,
    SportBundle\Entity\Department as DepartmentEntity,
    SportBundle\Entity\Runner as RunnerEntity;

/**
 * Lap
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Lap extends EntityRepository
{
    /**
     * @param  AcademicYear        $academicYear
     * @param  int                 $nbResults
     * @return \Doctrine\ORM\Query
     */
    public function findPreviousQuery(AcademicYear $academicYear, $nbResults = 1)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.academicYear', ':academicYear'),
                    $query->expr()->isNotNull('l.startTime'),
                    $query->expr()->isNotNull('l.endTime')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->orderBy('l.registrationTime', 'DESC')
            ->setMaxResults($nbResults)
            ->getQuery();

        return $resultSet;
    }

    /**
     * @param  AcademicYear        $academicYear
     * @return \Doctrine\ORM\Query
     */
    public function findAllPreviousLapsQuery(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.academicYear', ':academicYear'),
                    $query->expr()->isNotNull('l.startTime'),
                    $query->expr()->isNotNull('l.endTime')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->orderBy('l.startTime', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    /**
     * @param  AcademicYear                 $academicYear
     * @return \SportBundle\Entity\Lap|null
     */
    public function findCurrent(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.academicYear', ':academicYear'),
                    $query->expr()->isNotNull('l.startTime'),
                    $query->expr()->isNull('l.endTime')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->orderBy('l.registrationTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    /**
     * @param  AcademicYear $academicYear
     * @param  int          $nbResults
     * @return mixed
     */
    public function findNext(AcademicYear $academicYear, $nbResults = 1)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.academicYear', ':academicYear'),
                    $query->expr()->isNull('l.startTime'),
                    $query->expr()->isNull('l.endTime')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->orderBy('l.registrationTime', 'ASC')
            ->setMaxResults($nbResults)
            ->getQuery()
            ->getResult();

        if (1 == $nbResults) {
            return isset($resultSet[0]) ? $resultSet[0] : null;
        }

        return $resultSet;
    }

    /**
     * @param  AcademicYear $academicYear
     * @return int
     */
    public function countAll(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->select(
                $query->expr()->count('l.id')
            )
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.academicYear', ':academicYear'),
                    $query->expr()->isNotNull('l.startTime'),
                    $query->expr()->isNotNull('l.endTime')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->getQuery()
            ->getResult();

        return $resultSet[0][1];
    }

    /**
     * @param  AcademicYear $academicYear
     * @return int
     */
    public function countRunners(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->select(
                $query->expr()->countDistinct('l.runner')
            )
            ->where(
                $query->expr()->eq('l.academicYear', ':academicYear')
            )
            ->setParameter('academicYear', $academicYear)
            ->getQuery()
            ->getResult();

        return $resultSet[0][1];
    }

    /**
     * @param  AcademicYear $academicYear
     * @return mixed
     */
    public function getRunnersAndCount(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('IDENTITY(l.runner) runner', 'COUNT(l.runner) lapCount')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.academicYear', ':academicYear'),
                    $query->expr()->isNotNull('l.startTime'),
                    $query->expr()->isNotNull('l.endTime')
                )
            )
            ->groupBy('l.runner')
            ->orderBy('lapCount','DESC')
            ->setParameter('academicYear', $academicYear)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    /**
	 * @param RunnerEntity $runner
	 * @param AcademicYear $academicYear
	 * @return integer
	 */
    public function getStartedLapsCountForRunner(RunnerEntity $runner, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('COUNT(l.id) AS lapCount')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.runner', ':runner'),
                    $query->expr()->isNotNull('l.startTime'),
                    $query->expr()->eq('l.academicYear', ':academicYear')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->setParameter('runner', $runner)
            ->getQuery()
            ->getResult()[0];

        return $resultSet['lapCount'];
    }

    /**
     * @param  RunnerEntity $runner
     * @param  AcademicYear $academicYear
     * @return integer
     */
    public function findByAcadmicYearAndDepartment(AcademicYear $academicYear, DepartmentEntity $department)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SportBundle\Entity\Lap', 'l')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('l.department', ':department'),
                    $query->expr()->eq('l.academicYear', ':academicYear')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->setParameter('department', $department)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }
}
