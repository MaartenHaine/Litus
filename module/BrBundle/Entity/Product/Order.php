<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace BrBundle\Entity\Product;

use BrBundle\Entity\Company,
    CommonBundle\Entity\User\Person,
    DateTime,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping as ORM;

/**
 * An order of several products.
 *
 * @ORM\Entity(repositoryClass="BrBundle\Repository\Product\Order")
 * @ORM\Table(name="br.orders")
 */
class Order
{
    /**
     * @var int The ID of this node
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @var \BrBundle\Entity\Company The company of the logo
     *
     * @ORM\ManyToOne(targetEntity="BrBundle\Entity\Company")
     * @ORM\JoinColumn(name="company", referencedColumnName="id")
     */
    private $company;

    /**
     * @var \BrBundle\Entity\Product\OrderEntry The entries in this order
     *
     * @ORM\OneToMany(
     *      targetEntity="BrBundle\Entity\Product\OrderEntry",
     *      mappedBy="order",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    private $orderEntries;

    /**
     * @var \DateTime The time of creation of this node
     *
     * @ORM\Column(name="creation_time", type="datetime")
     */
    private $creationTime;

    /**
     * @var \CommonBundle\Entity\User\Person The person who created this node
     *
     * @ORM\ManyToOne(targetEntity="CommonBundle\Entity\User\Person")
     * @ORM\JoinColumn(name="creation_person", referencedColumnName="id")
     */
    private $creationPerson;

    /**
     */
    public function __construct(Company $company, Person $creationPerson)
    {
        $this->setCompany($company);
        $this->creationTime = new DateTime();
        $this->creationPerson = $creationPerson;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \BrBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @ return \BrBundle\Entity\Product\Order
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * @return \CommonBundle\Entity\User\Person
     */
    public function getCreationPerson()
    {
        return $this->creationPerson;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        return $this->orderEntries->toArray();
    }
}
