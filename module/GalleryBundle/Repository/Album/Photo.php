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
 *
 * @license http://litus.cc/LICENSE
 */

namespace GalleryBundle\Repository\Album;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    GalleryBundle\Entity\Album\Album as AlbumEntity;

/**
 * Photo
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Photo extends EntityRepository
{
    public function findOneByAlbumAndFilePath(AlbumEntity $album, $filePath)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('p')
            ->from('GalleryBundle\Entity\Album\Photo', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('p.album', ':album'),
                    $query->expr()->eq('p.filePath', ':filePath')
                )
            )
            ->setParameter('album', $album->getId())
            ->setParameter('filePath', $filePath)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
