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

namespace PublicationBundle\Entity\Edition;

use CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Component\Util\Url,
    DateTime,
    Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection,
    PublicationBundle\Entity\Publication;

/**
 * This is the entity for a publication
 *
 * @ORM\Entity(repositoryClass="PublicationBundle\Repository\Edition\PdfEdition")
 * @ORM\Table(name="publications.editions_pdf")
 */
class Pdf extends \PublicationBundle\Entity\Edition
{
    /**
     * Creates a new edition with the given title
     *
     * @param \PublicationBundle\Entity\Publication The publication to which this edition belongs
     * @param \CommonBundle\Entity\General\AcademicYear
     * @param string $title The title of this edition
     * @param \DateTime $date The date of this edition
     * @param string $fileName The file name of this edition
     */
    public function __construct(Publication $publication, AcademicYear $academicYear, $title, DateTime $date, $fileName)
    {
        parent::__construct($publication, $academicYear, $title, $date, $fileName);
    }
}