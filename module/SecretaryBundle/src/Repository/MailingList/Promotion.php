<?php

namespace SecretaryBundle\Repository\MailingList;

use CommonBundle\Entity\General\AcademicYear,
    Doctrine\ORM\EntityRepository;

/**
 * Promotion
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Promotion extends EntityRepository
{

    public function findAllByAdmin(Academic $academic) {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('MailBundle\Entity\MailingList', 'l')
            ->from('MailBundle\Entity\MailingList\AdminMap', 'a')
            ->where(
                $query->expr()->eq('a.academic', ':academic')
            )
            ->setParameter('academic', $academic)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findOneByAcademicYear(AcademicYear $academicYear) {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('l')
            ->from('SecretaryBundle\Entity\MailingList\Promotion', 'l')
            ->innerJoin('l.promotion', 'p')
            ->where(
                $query->expr()->eq('p.academicYear', ':year')
            )
            ->setParameter('year', $academicYear)
            ->getQuery()
            ->getResult();

        if (isset($resultSet[0]))
            return $resultSet[0];
        return null;
    }

}
