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

namespace SportBundle\Entity;

use CommonBundle\Entity\General\AcademicYear,
    DateInterval,
    Doctrine\ORM\EntityManager,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping as ORM;

/**
 * This entity represents a group of friends.
 *
 * @ORM\Entity(repositoryClass="SportBundle\Repository\Group")
 * @ORM\Table(name="sport.groups")
 */
class Group
{
    /**
     * @var int The ID of this group
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @var \CommonBundle\Entity\General\AcademicYear The year of the enrollment
     *
     * @ORM\ManyToOne(targetEntity="CommonBundle\Entity\General\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id")
     */
    private $academicYear;

    /**
     * @var string The name of this group
     *
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The members of this group
     *
     * @ORM\OneToMany(targetEntity="SportBundle\Entity\Runner", mappedBy="group")
     * @ORM\OrderBy({"lastName" = "ASC"})
     */
    private $members;

    /**
     * @var array
     *
     * @ORM\Column(name="happy_hours", type="string")
     */
    private $happyHours;

    /**
     * @param \CommonBundle\Entity\General\AcademicYear $academicYear
     * @param string $name
     * @param array $happyHours
     */
    public function __construct(AcademicYear $academicYear, $name, array $happyHours)
    {
        $this->academicYear = $academicYear;

        $this->name = $name;
        $this->happyHours = serialize($happyHours);
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return \SportBundle\Entity\Group
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * @param array $members
     * @return \SportBundle\Entity\Group
     */
    public function setMembers(array $members)
    {
        $this->members = new ArrayCollection($members);
        return $this;
    }

    /**
     * @return array
     */
    public function getHappyHours()
    {
        return unserialize($this->happyHours);
    }

    /**
     * @param array $happyHours
     * @return \SportBundle\Entity\Group
     */
    public function setHappyHours(array $happyHours)
    {
        $this->happyHours = serialize($happyHours);
        return $this;
    }

    /**
     * Returns the current point total of the group.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager The EntityManager instance
     * @return integer
     */
    public function getPoints(EntityManager $entityManager, AcademicYear $academicYear)
    {
        $points = 0;
        foreach ($this->getMembers() as $member) {
            foreach ($member->getLaps($entityManager, $academicYear) as $lap) {
                if (null === $lap->getEndTime())
                    continue;

                $startTime = $lap->getStartTime()->format('H');
                $endTime = $lap->getEndTime()->format('H');

                $points += $lap->getPoints($entityManager);

                $happyHours = $this->getHappyHours();
                for ($i = 0; isset($happyHours[$i]); $i++) {
                    if ($startTime >= substr($happyHours[$i], 0, 2) && $endTime <= substr($happyHours[$i], 2)) {
                        $points += $lap->getPoints($entityManager);
                    }
                }
            }
        }

        return $points;
    }
}
