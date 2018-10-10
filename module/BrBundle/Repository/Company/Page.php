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

namespace BrBundle\Repository\Company;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    CommonBundle\Entity\General\AcademicYear;

/**
 * Page
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Page extends EntityRepository
{
    /**
     * @param  string                        $slug
     * @param  AcademicYear                  $academicYear
     * @return \BrBundle\Entity\Company\Page
     */
    public function findOneActiveBySlug($slug, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('BrBundle\Entity\Company\Page', 'p')
            ->innerJoin('p.years', 'y')
            ->innerJoin('p.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->eq('c.slug', ':slug'),
                    $query->expr()->eq('y', ':year')
                )
            )
            ->setParameter('slug', $slug)
            ->setParameter('year', $academicYear)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    /**
     * @param  AcademicYear        $academicYear
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveQuery(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('BrBundle\Entity\Company\Page', 'p')
            ->innerJoin('p.years', 'y')
            ->innerJoin('p.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->eq('y', ':year')
                )
            )
            ->setParameter('year', $academicYear)
            ->getQuery();

        return $resultSet;
    }

    /**
     * @param  AcademicYear        $academicYear
     * @param  name                $name
     * @param  sector              $sector
     * @return \Doctrine\ORM\Query
     */
    public function findAllActiveBySearchQuery(AcademicYear $academicYear, $name = "%%", $sector = "")
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query = $query->select('p, c')
            ->from('BrBundle\Entity\Company\Page', 'p')
            ->innerJoin('p.years', 'y')
            ->innerJoin('p.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->eq('y', ':year'),
                    $query->expr()->like($query->expr()->lower('c.name'), ':name')
                )
            )
            ->orderBy('c.name', 'ASC')
            ->setParameter('name', strtolower($name))
            ->setParameter('year', $academicYear);

        if ($sector !== "" && $sector !== "all") {
            $query->andWhere(
                $query->expr()->eq('c.sector', ':sector')
            )
            ->setParameter('sector', $sector);
        }

        return $query->getQuery();
    }
}
