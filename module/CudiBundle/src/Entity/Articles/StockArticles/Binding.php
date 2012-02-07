<?php

namespace CudiBundle\Entity\Articles\StockArticles;

/**
 * @Entity(repositoryClass="CudiBundle\Repository\Articles\StockArticles\Binding")
 * @Table(name="cudi.articles_stockarticles_binding")
 */
class Binding
{
	/**
     * @Id
     * @GeneratedValue
     * @Column(type="bigint")
     */
	private $id;
	
	/**
     * @Column(type="string")
     */
    private $name;
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getName()
	{
		return $this->name;
	}
}
