<?php

namespace CudiBundle\Repository\Stock;

use CudiBundle\Entity\Sales\Article,
    CudiBundle\Entity\Stock\Period as PeriodEntity,
    Doctrine\ORM\EntityRepository,
    Doctrine\ORM\Query\Expr\Join;

/**
 * Period
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Period extends EntityRepository
{
    public function findAll()
    {
        $query = $this->_em->createQueryBuilder();
		$resultSet = $query->select('p')
			->from('CudiBundle\Entity\Stock\Period', 'p')
			->orderBy('p.startDate', 'DESC')
			->getQuery()
			->getResult();
       
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
			->getResult();

       if (isset($resultSet[0]))
           return $resultSet[0];
       
       return null;
    }
    
    private function _findAllArticleIds(PeriodEntity $period)
    {
        $query = $this->_em->createQueryBuilder();
		$query->select('i')
			->from('CudiBundle\Entity\Stock\Orders\Item', 'i')
			->innerJoin('i.order', 'o', Join::WITH,
			    $query->expr()->andX(
			        $query->expr()->gt('o.dateOrdered', ':startDate'),
			        $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
			    )
			)
			->setParameter('startDate', $period->getStartDate());
		
		if (!$period->isOpen())
		    $query->setParameter('endDate', $period->getEndDate());
		
		$resultSet = $query->getQuery()
			->getResult();

        $articles = array(0);
        foreach ($resultSet as $item)
            $articles[$item->getArticle()->getId()] = $item->getArticle()->getId();
        
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
            ->from('CudiBundle\Entity\Stock\Deliveries\Delivery', 'd')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('d.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('d.timestamp', ':endDate')
                )
            )
            ->setParameter('startDate', $period->getStartDate());
        
        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());
            
        $resultSet = $query->getQuery()
            ->getResult();

        foreach ($resultSet as $item)
            $articles[$item->getArticle()->getId()] = $item->getArticle()->getId();

        $query = $this->_em->createQueryBuilder();
        $query->select('i')
            ->from('CudiBundle\Entity\Sales\SaleItem', 'i')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('i.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('i.timestamp', ':endDate')
                )
            )
            ->setParameter('startDate', $period->getStartDate());
        
        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());
        
        $resultSet = $query->getQuery()
            ->getResult();
        
        foreach ($resultSet as $item)
            $articles[$item->getArticle()->getId()] = $item->getArticle()->getId();
        
        return $articles;
    }
    
    public function findAllArticlesByPeriod(PeriodEntity $period)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sales\Article', 'a')
            ->where(
                $query->expr()->in('a.id', $this->_findAllArticleIds($period))
            )
            ->getQuery()
            ->getResult();
            
        return $resultSet;
    }
    
    public function findAllArticlesByPeriodAndTitle(PeriodEntity $period, $title)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sales\Article', 'a')
            ->innerJoin('a.mainArticle', 'm', Join::WITH, 
                $query->expr()->like($query->expr()->lower('m.title'), ':title')
            )
            ->where(
                $query->expr()->in('a.id', $this->_findAllArticleIds($period))
            )
    		->setParameter('title', '%'.strtolower($title).'%')
            ->getQuery()
            ->getResult();
            
        return $resultSet;
    }
    
    public function findAllArticlesByPeriodAndBarcode(PeriodEntity $period, $barcode)
    {
        if (!is_numeric($barcode))
            return array();

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sales\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('a.id', $this->_findAllArticleIds($period)),
					$query->expr()->like($query->expr()->concat('a.barcode', '\'\''), ':barcode')
                )
            )
    		->setParameter('barcode', $barcode . '%')
            ->getQuery()
            ->getResult();
            
        return $resultSet;
    }
    
    public function findAllArticlesByPeriodAndSupplier(PeriodEntity $period, $supplier)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sales\Article', 'a')
            ->innerJoin('a.supplier', 's', Join::WITH, 
                $query->expr()->like($query->expr()->lower('s.name'), ':supplier')
            )
            ->where(
                $query->expr()->in('a.id', $this->_findAllArticleIds($period))
            )
    		->setParameter('supplier', '%'.strtolower($supplier).'%')
            ->getQuery()
            ->getResult();
            
        return $resultSet;
    }
    
    public function getNbDelivered(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(d.number)')
            ->from('CudiBundle\Entity\Stock\Deliveries\Delivery', 'd')
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
            
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(r.number)')
            ->from('CudiBundle\Entity\Stock\Deliveries\Retour', 'r')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('r.timestamp', ':startDate'),
                    $period->isOpen() ? '1=1' : $query->expr()->lt('r.timestamp', ':endDate'),
                    $query->expr()->eq('r.article', ':article')
                )
            )
            ->setParameter('startDate', $period->getStartDate())
            ->setParameter('article', $article->getId());
        
        if (!$period->isOpen())
            $query->setParameter('endDate', $period->getEndDate());
        
        $retour = $query->getQuery()
            ->getSingleScalarResult();
        
        if (null === $delivered)
            $delivered = 0;
        if (null === $retour)
            $retour = 0;
            
        return $delivered - $retour;
    }
    
    public function getNbOrdered(PeriodEntity $period, Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('SUM(i.number)')
        	->from('CudiBundle\Entity\Stock\Orders\Item', 'i')
        	->innerJoin('i.order', 'o', Join::WITH,
        	    $query->expr()->andX(
        	        $query->expr()->gt('o.dateOrdered', ':startDate'),
        	        $period->isOpen() ? '1=1' : $query->expr()->lt('o.dateOrdered', ':endDate')
        	    )
        	)
        	->where(
       	        $query->expr()->eq('i.article', ':article')
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
            ->from('CudiBundle\Entity\Sales\SaleItem', 'i')
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
            ->from('CudiBundle\Entity\Sales\Booking', 'b')
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
            ->from('CudiBundle\Entity\Sales\Booking', 'b')
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
        	->from('CudiBundle\Entity\Stock\Orders\Item', 'i')
        	->innerJoin('i.order', 'o', Join::WITH,
       	        $query->expr()->isNull('o.dateOrdered')
        	)
        	->where(
       	        $query->expr()->eq('i.article', ':article')
        	)
            ->setParameter('article', $article->getId())
            ->getQuery()
            ->getSingleScalarResult();
        
        if (null !== $resultSet)
            return $resultSet;
        return 0;
    }
}