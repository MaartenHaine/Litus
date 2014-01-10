<?php

namespace CudiBundle\Repository\Stock\Order;

use CommonBundle\Entity\General\AcademicYear,
    CudiBundle\Entity\Sale\Article,
    CudiBundle\Entity\Stock\Period,
    CudiBundle\Entity\Stock\Order\Order as OrderEntity,
    CudiBundle\Entity\Supplier,
    DateTime,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Item
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Item extends EntityRepository
{
    public function findOneOpenByArticle(Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.article', ':article'),
                    $query->expr()->isNull('o.dateCreated')
                )
            )
            ->setParameter('article', $article->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    public function findNumberBySupplier(Supplier $supplier, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('SUM(i.number)')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('o.supplier', ':supplier'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('supplier', $supplier)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->getQuery()
            ->getSingleScalarResult();

        if (null == $resultSet)
            return 0;

        return $resultSet;
    }

    public function getOrderedAmountByAcademicYear(AcademicYear $academicYear)
    {
        return $this->getOrderedAmountBetween($academicYear->getStartDate(), $academicYear->getEndDate());
    }

    public function getOrderedAmountBetween(DateTime $startDate, DateTime $endDate)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('SUM(i.number * a.purchasePrice)')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        if (null == $resultSet)
            return 0;

        return $resultSet;
    }

    public function getNumberByAcademicYear(AcademicYear $academicYear)
    {
        return $this->getNumberBetween($academicYear->getStartDate(), $academicYear->getEndDate());
    }

    public function getNumberBetween(DateTime $startDate, DateTime $endDate)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('SUM(i.number)')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        if (null == $resultSet)
            return 0;

        return $resultSet;
    }

    public function findAllByPeriodQuery(Period $period)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->orX(
                    $query->expr()->andX(
                        $query->expr()->gt('o.dateOrdered', ':startDate'),
                        $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
                    ),
                    $query->expr()->isNull('o.dateOrdered'),
                )
            )
            ->orderBy('o.dateOrdered', 'DESC')
            ->setParameter('startDate', $period->getStartDate());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery();

        return $resultSet;
    }

    public function findAllByTitleAndPeriodQuery($title, Period $period)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.title'), ':title'),
                    $query->expr()->orX(
                        $query->expr()->andX(
                            $query->expr()->gt('o.dateOrdered', ':startDate'),
                            $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
                        ),
                        $query->expr()->isNull('o.dateOrdered'),
                    )
                )
            )
            ->orderBy('o.dateOrdered', 'DESC')
            ->setParameter('title', '%'.strtolower($title).'%')
            ->setParameter('startDate', $period->getStartDate());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery();

        return $resultSet;
    }

    public function findAllBySupplierStringAndPeriodQuery($supplier, Period $period)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->innerJoin('o.supplier', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('s.name'), ':supplier'),
                    $query->expr()->orX(
                        $query->expr()->andX(
                            $query->expr()->gt('o.dateOrdered', ':startDate'),
                            $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
                        ),
                        $query->expr()->isNull('o.dateOrdered'),
                    )
                )
            )
            ->orderBy('o.dateOrdered', 'DESC')
            ->setParameter('supplier', '%'.strtolower($supplier).'%')
            ->setParameter('startDate', $period->getStartDate());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery();

        return $resultSet;
    }

    public function findAllByOrderOnAlphaQuery(OrderEntity $order)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('i, a, m')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->eq('i.order', ':order')
            )
            ->setParameter('order', $order)
            ->orderBy('m.title', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByOrderOnBarcodeQuery(OrderEntity $order)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('i, a, b')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.barcodes', 'b')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.order', ':order'),
                    $query->expr()->eq('b.main', ':isMainBarcode')
                )
            )
            ->setParameter('order', $order)
            ->setParameter('isMainBarcode', 'true')
            ->orderBy('b.barcode', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByAcademicYearQuery(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByArticleQuery($article, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.title'), ':article'),
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('article', '%'.strtolower($article).'%')
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllBySupplierQuery($supplier, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.supplier', 's')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('s.name'), ':supplier'),
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('supplier', '%'.strtolower($supplier).'%')
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByOrderQuery(OrderEntity $order, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('o.id', ':order'),
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('order', $order)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByArticleAndOrderQuery($article, OrderEntity $order, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.title'), ':article'),
                    $query->expr()->eq('o.id', ':order'),
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('article', '%'.strtolower($article).'%')
            ->setParameter('order', $order)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllBySupplierEntityQuery(Supplier $supplier, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('o.supplier', ':supplier'),
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('supplier', $supplier)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByArticleTitleAndSupplierAndAcademicYearQuery($article, Supplier $supplier, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i, o')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.title'), ':article'),
                    $query->expr()->eq('o.supplier', ':supplier'),
                    $query->expr()->isNotNull('o.dateOrdered'),
                    $query->expr()->gt('o.dateOrdered', ':start'),
                    $query->expr()->lt('o.dateOrdered', ':end')
                )
            )
            ->setParameter('article', '%'.strtolower($article).'%')
            ->setParameter('supplier', $supplier)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('o.dateOrdered', 'DESC')
            ->getQuery();

        return $resultSet;
    }
}
