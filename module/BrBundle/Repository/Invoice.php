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

use Doctrine\ORM\EntityRepository;

/**
 * Invoice
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Invoice extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllUnPayedQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $result = $query->select('i')
            ->from('BrBundle\Entity\Invoice', 'i')
            ->where(
                $query->expr()->isNull('i.paidTime')
            )
            ->orderBy('i.creationTime', 'DESC')
            ->getQuery();

        return $result;
    }

    /**
     * @param   String      $invoiceYear        The year from which you want to find all the unpayed invoices.
     * @return \Doctrine\ORM\Query
     */
    public function findAllUnPayedByInvoiceYearQuery($invoiceYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $result = $query->select('i')
            ->from('BrBundle\Entity\Invoice', 'i')
            ->where(
                $query->expr()->andX(
                    $query->expr()->isNull('i.paidTime'),
                    $query->expr()->like('i.invoiceNumberPrefix', ':invoiceYear')
                )
            )
            ->setParameter('invoiceYear', $invoiceYear.'%')
            ->orderBy('i.creationTime', 'DESC')
            ->getQuery();

        return $result;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllPayedQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $result = $query->select('i')
            ->from('BrBundle\Entity\Invoice', 'i')
            ->where(
                $query->expr()->isNotNull('i.paidTime')
            )
            ->orderBy('i.creationTime', 'DESC')
            ->getQuery();

        return $result;
    }

    /**
     * @param   String      $invoicePrefix  The invoice prefix for which you want to find the next invoice number
     * @return  int
     */
    public function findNextInvoiceNb($invoicePrefix)
    {

        $query = $this->getEntityManager()->createQueryBuilder();
        $highestInvoiceNb = $query->select('COALESCE(MAX(i.invoiceNb), 0)')
            ->from('BrBundle\Entity\Invoice', 'i')
            ->where(
                $query->expr()->eq('i.invoiceNumberPrefix', ':prefix')
            )
            ->setParameter('prefix', $invoicePrefix)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ++$highestInvoiceNb;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllInvoicePrefixes()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $result = $query->select('i.invoiceNumberPrefix')
            ->from('BrBundle\Entity\Invoice', 'i')
            ->distinct()
            ->getQuery()
            ->getResult();

        return $result;
    }

}
