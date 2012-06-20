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
 
namespace CudiBundle\Entity\Articles\Options;

/**
 * @Entity(repositoryClass="CudiBundle\Repository\Articles\Options\Binding")
 * @Table(name="cudi.articles_options_binding")
 */
class Binding
{
	/**
	 * @var integer The ID of the binding
	 *
     * @Id
     * @GeneratedValue
     * @Column(type="bigint")
     */
	private $id;
	
	/**
	 * @var string The name of the binding
	 *
     * @Column(type="string")
     */
    private $name;
    
    /**
     * @var string The code of the binding
     *
     * @Column(type="string")
     */
    private $code;
	
	/**
	 * @param string $name
	 */
	public function __construct($code, $name) {
		$this->code = $code;
		$this->name = $name;
	}
	
	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
}
