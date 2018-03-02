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

namespace BrBundle\Entity\Company;

use BrBundle\Entity\Company,
    Doctrine\ORM\Mapping as ORM,
    InvalidArgumentException;

/**
 * This is the entity for an event.
 *
 * @ORM\Entity(repositoryClass="BrBundle\Repository\Company\Logo")
 * @ORM\Table(name="br.companies_logos")
 */
class Logo
{
    /**
     * @var string The company logo's ID
     *
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string The type of the logo
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var string The url of the company
     *
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @var string The path to the logo
     *
     * @ORM\Column(type="string")
     */
    private $path;

    /**
     * @var Company The company of the logo
     *
     * @ORM\ManyToOne(targetEntity="BrBundle\Entity\Company")
     * @ORM\JoinColumn(name="company", referencedColumnName="id")
     */
    private $company;

    /**
     * @var integer The width of the logo
     *
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @var integer The height of the logo
     *
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @var array The possible types of a logo
     */
    public static $possibleTypes = array(
        'homepage' => 'Homepage',
        'cudi'     => 'Cudi',
    );

    /**
     * @param Company $company The event's company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * @param  string  $type
     * @return boolean
     */
    public static function isValidLogoType($type)
    {
        return array_key_exists($type, self::$possibleTypes);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::$possibleTypes[$this->type];
    }

    /**
     * @param  string $type
     * @return self
     */
    public function setType($type)
    {
        if (!self::isValidLogoType($type)) {
            throw new InvalidArgumentException('The logo type is not valid.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeCode()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param  string $url
     * @return self
     */
    public function setUrl($url)
    {
        if (strpos($url, 'http://') !== 0) {
            $url = 'http://' . $url;
        }

        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param  string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param  int  $width
     * @return self
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param  int  $height
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }
}
