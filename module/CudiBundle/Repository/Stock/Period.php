<?php

namespace CudiBundle\Repository\Stock;

use CudiBundle\Entity\Sale\Article,
    CudiBundle\Entity\Stock\Period as PeriodEntity,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Period
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Period extends EntityRepository
{
    public function findAllQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CudiBundle\Entity\Stock\Period', 'p')
            ->orderBy('p.startDate', 'DESC')
            ->getQuery();

       return $resultSet;
    }

    public function findOneActive()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('CudiBundle\Entity\Stock\Period', 'p')
            ->where(
                $query->expr()->isNull('p.endDate')
            )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }

    private function _findAllArticleIds(PeriodEntity $period)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('a.id')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('o.dateOrdered', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
                )
            )
            ->groupBy('a.id')
            ->setParameter('startDate', $period->getStartDate());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getResult();

        $articles = array(0);
        foreach ($resultSet as $item)
            $articles[$item['id']] = $item['id'];

        $query = $this->_em->createQueryBuilder();
        $query->select('a.id')
            ->from('CudiBundle\Entity\Stock\Delivery', 'd')
            ->innerJoin('d.article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('d.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('d.timestamp', ':endDate')
                )
            )
            ->groupBy('a.id')
            ->setParameter('startDate', $period->getStartDate());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getResult();

        foreach ($resultSet as $item)
            $articles[$item['id']] = $item['id'];

        $query = $this->_em->createQueryBuilder();
        $query->select('a.id')
            ->from('CudiBundle\Entity\Sale\SaleItem', 'i')
            ->innerJoin('i.article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('i.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('i.timestamp', ':endDate')
                )
            )
            ->groupBy('a.id')
            ->setParameter('startDate', $period->getStartDate());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getResult();

        foreach ($resultSet as $item)
            $articles[$item['id']] = $item['id'];

        return $articles;
    }

    public function findAllArticlesByPeriod(PeriodEntity $period, $notDelivered = false)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->where(
                $query->expr()->in('a.id', $this->_findAllArticleIds($period))
            )
            ->getQuery()
            ->getResult();

        if ($notDelivered) {
            for($i = 0 ; $i < count($resultSet) ; $i++) {
                if ($period->getNbOrdered($resultSet[$i]) + $period->getNbVirtualOrdered($resultSet[$i]) - $period->getNbDelivered($resultSet[$i]) <= 0)
                    unset($resultSet[$i]);
            }
        }

        return $resultSet;
    }

    public function findAllArticlesByPeriodAndTitle(PeriodEntity $period, $title, $notDelivered = false)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('a.id', $this->_findAllArticleIds($period)),
                    $query->expr()->like($query->expr()->lower('m.title'), ':title')
                )
            )
            ->setParameter('title', '%'.strtolower($title).'%')
            ->getQuery()
            ->getResult();

        if ($notDelivered) {
            for($i = 0 ; $i < count($resultSet) ; $i++) {
                if ($period->getNbOrdered($resultSet[$i]) + $period->getNbVirtualOrdered($resultSet[$i]) - $period->getNbDelivered($resultSet[$i]) <= 0)
                    unset($resultSet[$i]);
            }
        }

        return $resultSet;
    }

    public function findAllArticlesByPeriodAndBarcode(PeriodEntity $period, $barcode, $notDelivered = false)
    {
        if (!is_numeric($barcode))
            return array();

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('b')
            ->from('CudiBundle\Entity\Sale\Article\Barcode', 'b')
            ->innerJoin('b.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->concat('b.barcode', '\'\''), ':barcode'),
                    $query->expr()->in('a.id', $this->_findAllArticleIds($period))
                )
            )
            ->setParameter('barcode', '%'.$barcode.'%')
            ->getQuery()
            ->getResult();

        $articles = array();
        foreach($resultSet as $barcode) {
            if ($notDelivered && $period->getNbOrdered($barcode->getArticle()) + $period->getNbVirtualOrdered($resultSet[$i]) - $period->getNbDelivered($barcode->getArticle()) <= 0)
                continue;
            $articles[$barcode->getArticle()->getId()] = $barcode->getArticle();
        }

        return $articles;
    }

    public function findAllArticlesByPeriodAndSupplier(PeriodEntity $period, $supplier, $notDelivered = false)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.supplier', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('a.id', $this->_findAllArticleIds($period)),
                    $query->expr()->like($query->expr()->lower('s.name'), ':supplier')
                )
            )
            ->setParameter('supplier', '%'.strtolower($supplier).'%')
            ->getQuery()
            ->getResult();

        if ($notDelivered) {
            for($i = 0 ; $i < count($resultSet) ; $i++) {
                if ($period->getNbOrdered($resultSet[$i]) + $period->getNbVirtualOrdered($resultSet[$i]) - $period->getNbDelivered($resultSet[$i]) <= 0)
                    unset($resultSet[$i]);
            }
        }

        return $resultSet;
    }

    public function getNbDelivered(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(d.number)')
            ->from('CudiBundle\Entity\Stock\Delivery', 'd')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('d.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('d.timestamp', ':endDate'),
                    $query->expr()->eq('d.article', ':article')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $delivered = $query->getQuery()
            ->getSingleScalarResult();

        if (null === $delivered)
            $delivered = 0;

        return $delivered;
    }

    public function getNbOrdered(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(i.number)')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.article', ':article'),
                    $query->expr()->gt('o.dateOrdered', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getSingleScalarResult();

        if (null !== $resultSet)
            return $resultSet;

        return 0;
    }

    public function getNbVirtualOrdered(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(i.number)')
            ->from('CudiBundle\Entity\Stock\Order\Virtual', 'i')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.article', ':article'),
                    $query->expr()->gt('i.dateCreated', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateCreated', ':endDate')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getSingleScalarResult();

        if (null !== $resultSet)
            return $resultSet;

        return 0;
    }

    public function getNbSold(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(i.number)')
            ->from('CudiBundle\Entity\Sale\SaleItem', 'i')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('i.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('i.timestamp', ':endDate'),
                       $query->expr()->eq('i.article', ':article')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getSingleScalarResult();

        if (null !== $resultSet)
            return $resultSet;

        return 0;
    }

    public function getNbBooked(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(b.number)')
            ->from('CudiBundle\Entity\Sale\Booking', 'b')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('b.bookDate', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('b.bookDate', ':endDate'),
                       $query->expr()->eq('b.article', ':article'),
                       $query->expr()->eq('b.status', '\'booked\'')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getSingleScalarResult();

        if (null !== $resultSet)
            return $resultSet;

        return 0;
    }

    public function getNbAssigned(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(b.number)')
            ->from('CudiBundle\Entity\Sale\Booking', 'b')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('b.bookDate', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('b.bookDate', ':endDate'),
                       $query->expr()->eq('b.article', ':article'),
                       $query->expr()->eq('b.status', '\'assigned\'')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());

        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());

        $resultSet = $query->getQuery()
            ->getSingleScalarResult();

        if (null !== $resultSet)
            return $resultSet;

        return 0;
    }

    public function getNbQueueOrder(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('SUM(i.number)')
            ->from('CudiBundle\Entity\Stock\Order\Item', 'i')
            ->innerJoin('i.order', 'o')
            ->where(
                $query->expr()->andX(
                   $query->expr()->eq('i.article', ':article'),
                   $query->expr()->isNull('o.dateOrdered')
                )
            )
            ->setParameter('article', $article->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if (null !== $resultSet)
            return $resultSet;

        return 0;
    }

    public function getNbRetoured(PeriodEntity $period, Article $article)
    {
        return $this->_em
            ->getRepository('CudiBundle\Entity\Stock\Retour')
            ->findTotalByArticleAndPeriod($article, $period);
    }
}
