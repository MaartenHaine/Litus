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

namespace CudiBundle\Repository\Sale\Article\Restriction;

use CudiBundle\Entity\Sale\Article,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Amount
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Amount extends EntityRepository
{
    public function findOneByArticle(Article $article)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('r')
            ->from('CudiBundle\Entity\Sale\Article\Restriction\Amount', 'r')
            ->where(
                $query->expr()->eq('r.article', ':article')
            )
            ->setParameter('article', $article)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resultSet;
    }
}
