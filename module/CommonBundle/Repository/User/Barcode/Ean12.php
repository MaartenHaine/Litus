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

namespace CommonBundle\Repository\User\Barcode;

/**
 * Ean12
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Ean12 extends \CommonBundle\Repository\User\Barcode
{
    public function findOneByBarcode($barcode)
    {
        if (!is_numeric($barcode))
            return null;

        if (strlen($barcode) == 13)
            $barcode = floor($barcode / 10);
        if (strlen($barcode) > 12)
            return null;

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('b')
            ->from('CommonBundle\Entity\User\Barcode\Ean12', 'b')
            ->where(
                $query->expr()->eq('b.barcode', ':barcode')
            )
            ->setParameter('barcode', $barcode)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
