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

namespace CommonBundle\Repository\General\Node\FAQ;

use PageBundle\Entity\Node\Page;

/**
 * FAQ
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FAQ extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{

    /**
     * @param Page $page
     * @return \Doctrine\ORM\Query
     */
    public function findAllByPageQuery(Page $page)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('f')
            ->from('CommonBundle\Entity\General\Node\FAQ\FAQ', 'f')
            ->innerJoin('f.pages', 'p')
            ->where(
                $query->expr()->eq(':id', 'p.id'))
            ->orderBy('f.name', 'ASC')
            ->setParameter('id', $page->getId())
            ->getQuery();
    }

    /**
     * @param string $name
     * @return \Doctrine\ORM\Query
     */
    public function findAllByNameQuery(string $name)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('f')
            ->from('CommonBundle\Entity\General\Node\FAQ\FAQ', 'f')
            ->where(
                $query->expr()->like($query->expr()->lower('f.name'), ':name')
            )
            ->orderBy('f.name', 'ASC')
            ->setParameter('name', '%'.strtolower($name).'%')
            ->getQuery();
    }
}
