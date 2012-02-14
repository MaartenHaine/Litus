<?php
 /**
  * Litus is a project by a group of students from the K.U.Leuven. The goal is to create
  * various applications to support the IT needs of student unions.
  *
  * @author Karsten Daemen <karsten.daemen@litus.cc>
  * @author Bram Gotink <bram.gotink@litus.cc>
  * @author Pieter Maene <pieter.maene@litus.cc>
  * @author Kristof Mariën <kristof.marien@litus.cc>
  * @author Michiel Staessen <michiel.staessen@litus.cc>
  * @author Alan Szepieniec <alan.szepieniec@litus.cc>
  *
  * @license http://litus.cc/LICENSE
  */
  
namespace CudiBundle\Entity\Stock;

use CudiBundle\Entity\Article,
	Doctrine\ORM\EntityManager;
 
/**
 * @Entity(repositoryClass="CudiBundle\Repository\Stock\StockItem")
 * @Table(name="cudi.stock_stockitem")
 */
class StockItem
{
	/**
	 * @Id
	 * @GeneratedValue
	 * @Column(type="bigint")
	 */
	private $id;
	
	/**
	 * @OneToOne(targetEntity="CudiBundle\Entity\Article", inversedBy="stockItem")
	 * @JoinColumn(name="article", referencedColumnName="id")
	 */
	private $article;
	
	/**
	 * @Column(type="integer")
	 */
	private $numberInStock;
	
	/**
	 * @param \CudiBundle\Entity\Article $article
	 */
	public function __construct(Article $article)
	{
		$this->article = $article;
		$this->numberInStock = 0;
	}
	
	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Return the article
	 * 
	 * @return CudiBundle\Entity\Article
	 */
	public function getArticle()
	{
		return $this->article;
	}
	
	/**
	 * Return the number in stock
	 * 
	 * @return integer
	 */
	public function getNumberInStock()
	{
		return $this->numberInStock;
	}
	
	/**
	 * @param integer $number The number in stock
	 *
	 * @param \CudiBundle\Entity\Stock\StockItem
	 */
	public function setNumberInStock($number)
	{
		$this->numberInStock = $number;
		return $this;
	}
	
	/**
	 * @param integer $number The number to add
	 *
	 * @param \CudiBundle\Entity\Stock\StockItem
	 */
	public function addNumber($number)
	{
		$this->numberInStock += $number;
		if ($this->numberInStock < 0)
			$this->numberInStock = 0;
		return $this;
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getTotalOrdered(EntityManager $entityManager)
	{
		$total = $entityManager
			->getRepository('CudiBundle\Entity\Stock\Order')
			->getTotalOrdered($this->article);
		
		return $total;
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getNumberNotDelivered(EntityManager $entityManager)
	{
		return $this->getTotalOrdered($entityManager) - $this->getTotalDelivered($entityManager);
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getNumberQueueOrder(EntityManager $entityManager)
	{
		$item = $entityManager
			->getRepository('CudiBundle\Entity\Stock\OrderItem')
			->findOneOpenByArticle($this->article);
		
		if (null === $item)
			return 0;
		
		return $item->getNumber();
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getTotalDelivered(EntityManager $entityManager)
	{
		return $entityManager
			->getRepository('CudiBundle\Entity\Stock\DeliveryItem')
			->getTotalByArticle($this->article);
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getNumberBooked(EntityManager $entityManager)
	{
		$booked = $entityManager
			->getRepository('CudiBundle\Entity\Sales\Booking')
			->findAllBookedByArticle($this->article);
		
		$number = 0;
		foreach($booked as $booking)
			$number += $booking->getNumber();
		
		return $number;
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getNumberAssigned(EntityManager $entityManager)
	{
		$booked = $entityManager
			->getRepository('CudiBundle\Entity\Sales\Booking')
			->findAllAssignedByArticle($this->article);
		
		$number = 0;
		foreach($booked as $booking)
			$number += $booking->getNumber();
		
		return $number;
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getNumberSold(EntityManager $entityManager)
	{
		$booked = $entityManager
			->getRepository('CudiBundle\Entity\Sales\Booking')
			->findAllSoldByArticle($this->article);
		
		$number = 0;
		foreach($booked as $booking)
			$number += $booking->getNumber();
		
		return $number;
	}
	
	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 *
	 * @return integer
	 */
	public function getNumberAvailable(EntityManager $entityManager)
	{
		return $this->numberInStock - $this->getNumberBooked($entityManager);
	}
}