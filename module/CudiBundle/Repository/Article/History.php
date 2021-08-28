<?php

namespace CudiBundle\Repository\Article;

use CudiBundle\Entity\Article;

/**
 * History
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class History extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @param  Article $article
     * @return \Doctrine\ORM\Query
     */
    public function findAllByArticleQuery(Article $article)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('h, a')
            ->from('CudiBundle\Entity\Article\History', 'h')
            ->innerJoin('h.precursor', 'a')
            ->where(
                $query->expr()->eq('h.article', ':article')
            )
            ->setParameter('article', $article)
            ->orderBy('a.timestamp')
            ->getQuery();
    }
}
