<?php

namespace PublicationBundle\Repository\Edition;

use CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Component\Doctrine\ORM\EntityRepository,
	PublicationBundle\Entity\Publication as PublicationEntity;

/**
 * PdfEdition
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PdfEdition extends EntityRepository
{
	public function findAllByPublicationAndAcademicYearQuery(PublicationEntity $publication, AcademicYear $academicYear)
	{
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('PublicationBundle\Entity\Edition\Pdf', 'p')
            ->where(
                $query->expr()->andX(
            	   $query->expr()->eq('p.publication', ':publication'),
                   $query->expr()->eq('p.academicYear', ':year')
               )
            )
            ->setParameter('publication', $publication)
            ->setParameter('year', $academicYear)
            ->orderBy('p.date', 'ASC')
            ->getQuery();

        return $resultSet;
	}

    public function findOneByPublicationTitleAndAcademicYear(PublicationEntity $publication, $title, AcademicYear $academicYear)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('PublicationBundle\Entity\Edition\Pdf', 'p')
            ->where(
                $query->expr()->andX(
                   $query->expr()->eq('p.publication', ':publication'),
                   $query->expr()->eq('p.title', ':title'),
                   $query->expr()->eq('p.academicYear', ':year')
               )

            )
            ->setParameter('publication', $publication)
            ->setParameter('title', $title)
            ->setParameter('year', $academicYear)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
