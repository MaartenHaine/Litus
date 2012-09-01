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

namespace PageBundle\Entity;

use CommonBundle\Entity\General\Language,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping as ORM,
    PageBundle\Entity\Nodes\Page;

/**
 * This entity stores the node item.
 *
 * @ORM\Entity(repositoryClass="PageBundle\Repository\Category")
 * @ORM\Table(name="nodes.pages_categories")
 */
class Category
{
    /**
     * @var int The ID of this category
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @var \PageBundle\Entity\Nodes\Page The page's parent
     *
     * @ORM\ManyToOne(targetEntity="PageBundle\Entity\Nodes\Page")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The translations of this category
     *
     * @ORM\OneToMany(targetEntity="PageBundle\Entity\Categories\Translation", mappedBy="category", cascade={"remove"})
     */
    private $translations;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @param \PageBundle\Entity\Nodes\Page $category The page's category
     * @return \PageBundle\Entity\Nodes\Page
     */
    public function setParent(Page $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return \PageBundle\Entity\Nodes\Page
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \CommonBundle\Entity\General\Language $language
     * @param boolean $allowFallback
     * @return \PageBundle\Entity\Nodes\Translation
     */
    public function getTranslation(Language $language = null, $allowFallback = true)
    {
        foreach($this->translations as $translation) {
            if (null !== $language && $translation->getLanguage() == $language)
                return $translation;

            if ($translation->getLanguage()->getAbbrev() == \Locale::getDefault())
                $fallbackTranslation = $translation;
        }

        if ($allowFallback)
            return $fallbackTranslation;

        return null;
    }

    /**
     * @param \CommonBundle\Entity\General\Language $language
     * @param boolean $allowFallback
     * @return string
     */
    public function getName(Language $language = null, $allowFallback = true)
    {
        $translation = $this->getTranslation($language, $allowFallback);

        if (null !== $translation)
            return $translation->getName();

        return '';
    }
}
