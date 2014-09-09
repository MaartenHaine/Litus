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

namespace BrBundle\Repository\Company;

use BrBundle\Entity\Company as CompanyEntity,
    DateTime,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Job
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Job extends EntityRepository
{
    public function findOneActiveByTypeAndId($type, $id)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->eq('v.id', ':id'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true')
                )
            )
            ->setParameter('id', $id)
            ->setParameter('type', $type)
            ->setParameter('now', new DateTime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findAllByCompanyQuery(CompanyEntity $company)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->where(
                $query->expr()->eq('v.company', ':company')
            )
            ->setParameter('company', $company->getId())
            ->orderBy('v.type', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByTypeQuery($type)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v, c')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('now', new DateTime())
            ->orderBy('c.name','ASC')
            ->addOrderBy('v.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByTypeByJobNameQuery($type)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v, c')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('now', new DateTime())
            ->orderBy('v.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByTypeByDateQuery($type)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v, c')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('now', new DateTime())
            ->orderBy('v.dateUpdated', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByTypeAndSectorByDateQuery($type, $sector)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v, c')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->eq('v.sector', ':sector')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('sector', $sector)
            ->setParameter('now', new DateTime())
            ->orderBy('v.dateUpdated', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByTypeAndSectorQuery($type, $sector)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v, c')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->eq('v.sector', ':sector')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('sector', $sector)
            ->setParameter('now', new DateTime())
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('v.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByTypeAndSectorByJobNameQuery($type, $sector)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v, c')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->innerJoin('v.company', 'c')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now'),
                    $query->expr()->eq('c.active', 'true'),
                    $query->expr()->eq('v.sector', ':sector')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('sector', $sector)
            ->setParameter('now', new DateTime())
            ->orderBy('v.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllActiveByCompanyAndTypeQuery(CompanyEntity $company, $type)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('v')
            ->from('BrBundle\Entity\Company\Job', 'v')
            ->where(
                $query->expr()->andx(
                    $query->expr()->eq('v.company', ':company'),
                    $query->expr()->eq('v.type', ':type'),
                    $query->expr()->gt('v.endDate', ':now')
                )
            )
            ->setParameter('type', $type)
            ->setParameter('company', $company->getId())
            ->setParameter('now', new DateTime())
            ->orderBy('v.name', 'ASC')
            ->getQuery();

        return $resultSet;
    }
}
