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

namespace CommonBundle\Repository\User\Person;

use CommonBundle\Entity\General\AcademicYear;

/**
 * Academic
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Academic extends \CommonBundle\Repository\User\Person
{
    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->eq('p.id', ':id')
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findAllByUsernameQuery($username)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->like($query->expr()->lower('p.username'), ':username')
            )
            ->setParameter('username', '%' . strtolower($username) . '%')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByNameQuery($name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orX(
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.firstName', "' '")),
                                $query->expr()->lower('p.lastName')
                            ),
                            ':name'
                        ),
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.lastName', "' '")),
                                $query->expr()->lower('p.firstName')
                            ),
                            ':name'
                        )
                    ),
                    $query->expr()->eq('p.canLogin', 'true')
                )
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByUniversityIdentificationQuery($universityIdentification)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like('p.universityIdentification', ':universityIdentification'),
                    $query->expr()->eq('p.canLogin', 'true')
                )
            )
            ->setParameter('universityIdentification', '%' . strtolower($universityIdentification) . '%')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByBarcodeQuery($barcode)
    {
        $barcodes = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\User\Barcode')
            ->findAllByBarcode($barcode);

        $ids = array(0);
        foreach ($barcodes as $barcode) {
            $ids[] = $barcode->getId();
        }

        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('p.id', $ids),
                    $query->expr()->eq('p.canLogin', 'true')
                )
            )
            ->getQuery();

        return $resultSet;
    }

    public function findOneByUsername($username)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orX(
                        $query->expr()->eq($query->expr()->lower('p.username'), ':username'),
                        $query->expr()->eq('p.universityIdentification', ':username')
                    ),
                    $query->expr()->eq('p.canLogin', 'true')
                )
            )
            ->setParameter('username', strtolower($username))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($resultSet) {
            return $resultSet;
        }

        $barcode = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\User\Barcode')
            ->findOneByBarcode($username);

        if ($barcode) {
            return $barcode->getPerson();
        }

        return null;
    }

    public function findAllByNameTypeaheadQuery($name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CommonBundle\Entity\User\Person\Academic', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orX(
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.firstName', "' '")),
                                $query->expr()->lower('p.lastName')
                            ),
                            ':name'
                        ),
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.lastName', "' '")),
                                $query->expr()->lower('p.firstName')
                            ),
                            ':name'
                        ),
                        $query->expr()->like('p.universityIdentification', ':name')
                    ),
                    $query->expr()->eq('p.canLogin', 'true')
                )
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->setMaxResults(20)
            ->getQuery();

        return $resultSet;
    }

    public function findAllMembers(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CommonBundle\Entity\User\Status\Organization', 's')
            ->innerJoin('s.person', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->neq('s.status', '\'non_member\''),
                    $query->expr()->eq('s.academicYear', ':academicYear'),
                    $query->expr()->eq('p.canLogin', 'true')
                )
            )
            ->setParameter('academicYear', $academicYear->getId())
            ->getQuery()
            ->getResult();

        $persons = array();
        foreach ($resultSet as $result) {
            $persons[] = $result->getPerson();
        }

        return $persons;
    }
}
