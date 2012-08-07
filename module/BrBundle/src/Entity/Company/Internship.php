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
 
namespace BrBundle\Entity\Company;

/**
 * This is the entity for an internship.
 *
 * @Entity(repositoryClass="BrBundle\Repository\Company\Internship")
 * @Table(name="br.company_internships")
 */
class Internship
{
    /**
     * @var string The internship's ID
     *
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue
     */
    private $id;
    
    /**
     * @var string The internship's name
     *
     * @Column(type="string", length=50)
     */
    private $name;
    
    /**
     * @var string The description of the internship
     *
     * @Column(type="text")
     */
    private $description;
    
    /**
     * @var \BrBundle\Entity\Company The company of the internship
     *
     * @OneToOne(targetEntity="BrBundle\Entity\Company")
     * @JoinColumn(name="company", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @param string $name The internship's name
     * @param string $description The internship's description
     * @param \BrBundle\Entity\Company $company The internship's company
     */
    public function __construct($name, $description, Company $company)
    {
        $this->setName($name);
        $this->setDescription($description);
        
        $this->company = $company;
    }
    
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string $name
     * @return \BrBundle\Entity\Company\Internship
     */
    public function setName($name)
    {
        if ((null === $name) || !is_string($name))
            throw new \InvalidArgumentException('Invalid name');
            
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string $description
     * @return \BrBundle\Entity\Company\Internship
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @return \BrBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }
}
