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

namespace BrBundle\Entity;

use CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Entity\User\Person,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping as ORM;

/**
 * A product is something that can be sold to companies.
 *
 * @ORM\Entity(repositoryClass="BrBundle\Repository\Product")
 * @ORM\Table(name="br.products")
 */
class Product
{

    /**
     * @var int A generated ID
     *
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string The name of this product
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * @var string The contractText of this product
     *
     * @ORM\Column(name="contract_text", type="text")
     */
    private $contractText;

    /**
     * @var \CommonBundle\Entity\User\Person The author of this product
     *
     * @ORM\ManyToOne(targetEntity="CommonBundle\Entity\User\Person")
     * @ORM\JoinColumn(name="author", referencedColumnName="id")
     */
    private $author;

    /**
     * @var \CommonBundle\Entity\General\AcademicYear
     *
     * @ORM\ManyToOne(targetEntity="CommonBundle\Entity\General\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id", nullable=false)
     */
    private $academicYear;

    /**
     * @var int The price (VAT excluded!) a company has to pay when they agree to this product of the contract
     *
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @var string The VAT type (e.g. in Belgium: 6%, 12%, 21% ...); the values are indexes in a configurable
     * array of possible values
     *
     * @ORM\Column(name="vat_type", type="integer")
     */
    private $vatType;

    /**
     * @var string The short description of this product shown in invoices
     *
     * @ORM\Column(name="invoice_description", type="string", nullable=true)
     */
    private $invoiceDescription;

    /**
     * @param string $name The name of this product
     * @param string $description The description on the invoice of this product
     * @param string $contractText The contract text of this product
     * @param \CommonBundle\Entity\User\Person $author The author of this product
     * @param int $price
     * @param string $vatType see setVatType($vatType)
     */
    public function __construct(EntityManager $entityManager, $name, $description, $contractText, Person $author, $price, $vatType, AcademicYear $academicYear)
    {
        $this->setName($name);
        $this->setInvoiceDescription($description);
        $this->setContractText($contractText);
        $this->setAuthor($author);
        $this->setPrice($price);
        $this->setVatType($entityManager, $vatType);
        $this->academicYear = $academicYear;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name The name of this product
     * @return \BrBundle\Entity\Product
     */
    public function setName($name)
    {
        if ((null === $name) || !is_string($name))
            throw new \InvalidArgumentException('Invalid name');

        $this->name = $name;

        return $this;
    }

    /**
     * @return \Litus\Entity\Users\Person
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param \Litus\Entity\Users\Person $author The author of this product
     * @return \BrBundle\Entity\Product
     */
    public function setAuthor(Person $author)
    {
        if (null === $author)
            throw new \InvalidArgumentException('Invalid author');

        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getContractText()
    {
        return $this->contractText;
    }

    /**
     * @param string $contractText The contract text of this product
     * @return \BrBundle\Entity\Product
     */
    public function setContractText($contractText)
    {
        if ((null === $contractText) || !is_string($contractText))
            throw new \InvalidArgumentException('Invalid contract text');

        $this->contractText = $contractText;

        return $this;
    }

    /**
     * @return string
     */
    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager The EntityManager instance
     * @param string $vatType The VAT type (e.g. in Belgium: 6%, 12%, 21% ...); the values are 'A','B', ...; a value is valid if the configuration entry 'br.invoice.vat.<value>' exists
     * @throws \InvalidArgumentException
     * @return \BrBundle\Entity\Product
     */
    public function setVatType(EntityManager $entityManager, $vatType)
    {
        $types = $entityManager->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('br.vat_types');
        $types = unserialize($types);
        if (!isset($types[$vatType])) {
            throw new \InvalidArgumentException('Invalid VAT type: ' . $vatType);
        }

        $this->vatType = $vatType;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatType()
    {
        return $this->vatType;
    }

    /**
     * Returns the VAT percentage for this product.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager The EntityManager instance
     * @return int
     */
    public function getVatPercentage(EntityManager $entityManager)
    {
        $types =  $entityManager->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('br.vat_types');
        $types = unserialize($types);
        return $types[$this->getVatType()];
    }

    /**
     * @param float $price
     * @return \BrBundle\Entity\Product
     */
    public function setPrice($price)
    {
        if (
            (null === $price)
            || !preg_match('/^[0-9]+.?[0-9]{0,2}$/', $price)
        ) {
            throw new \InvalidArgumentException('Invalid price');
        }

        $this->price = $price * 100;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getInvoiceDescription()
    {
        return $this->invoiceDescription;
    }

    /**
     * @param string|null $description
     * @return \BrBundle\Entity\Product
     */
    public function setInvoiceDescription($description)
    {
        if ((null === $description) || !is_string($description))
            throw new \InvalidArgumentException('Invalid description');

        $this->invoiceDescription = $description;

        return $this;
    }
}
