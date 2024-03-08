<?php

namespace BrBundle\Repository;

use CommonBundle\Component\Doctrine\ORM\EntityRepository;
use CommonBundle\Entity\General\AcademicYear;
use CommonBundle\Entity\User\Person\Academic;
use Doctrine\ORM\Query;

/**
 * StudentCompanyMatch
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StudentCompanyMatch extends EntityRepository
{
    /**
     * @param  AcademicYear $academicYear
     * @return Query
     */
    public function findAllByAcademicYearQuery(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('m')
            ->from('BrBundle\Entity\StudentCompanyMatch', 'm')
            ->where(
                $query->expr()->eq('m.year', ':year')
            )
            ->setParameter('year', $academicYear)
            ->getQuery();
    }

    /**
     * @param  \BrBundle\Entity\Company $company
     * @param  AcademicYear $academicYear
     * @return Query
     */
    public function findAllByCompanyAndYear(\BrBundle\Entity\Company $company, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('m')
            ->from('BrBundle\Entity\StudentCompanyMatch', 'm')
            ->where(
                $query->expr()->eq('m.company', ':company'),
                $query->expr()->eq('m.year', ':year')
            )
            ->setParameter('company', $company)
            ->setParameter('year', $academicYear)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  Academic $student
     * @param  AcademicYear $academicYear
     * @return Query
     */
    public function findAllByStudentAndYear(Academic $student, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('m')
            ->from('BrBundle\Entity\StudentCompanyMatch', 'm')
            ->where(
                $query->expr()->eq('m.academic', ':academic'),
                $query->expr()->eq('m.year', ':year')
            )
            ->setParameter('academic', $student)
            ->setParameter('year', $academicYear)
            ->getQuery()
            ->getResult();
    }
}
