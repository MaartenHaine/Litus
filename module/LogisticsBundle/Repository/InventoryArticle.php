<?php

namespace LogisticsBundle\Repository;

use LogisticsBundle\Entity\InventoryCategory;

/**
 * InventoryArticle
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InventoryArticle extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('LogisticsBundle\Entity\InventoryArticle', 'a')
            ->innerJoin('a.unit', 'u')
            ->orderBy('u.name', 'ASC')
            ->addOrderBy('a.category', 'ASC')
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  string $name
     * @return array
     */
    public function findAllByName(string $name): array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('LogisticsBundle\Entity\InventoryArticle', 'a')
            ->innerJoin('a.unit', 'u')
            ->where(
                $query->expr()->like($query->expr()->lower('a.name'), ':name')
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->orderBy('u.name', 'ASC')
            ->addOrderBy('a.category', 'ASC')
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  string $location
     * @return array
     */
    public function findAllByLocation(string $location): array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('LogisticsBundle\Entity\InventoryArticle', 'a')
            ->where(
                $query->expr()->like($query->expr()->lower('a.location'), ':location')
            )
            ->setParameter('location', '%' . strtolower($location) . '%')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  string $visibility
     * @return array
     */
    public function findAllByVisibility(string $visibility): array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('LogisticsBundle\Entity\InventoryArticle', 'a')
            ->where(
                $query->expr()->like($query->expr()->lower('a.visibility'), ':visibility')
            )
            ->setParameter('visibility', '%' . strtolower($visibility) . '%')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  string $status
     * @return array
     */
    public function findAllByStatus(string $status): array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('LogisticsBundle\Entity\InventoryArticle', 'a')
            ->where(
                $query->expr()->like($query->expr()->lower('a.status'), ':status')
            )
            ->setParameter('status', '%' . strtolower($status) . '%')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $visibility
     * @param string $status
     * @param InventoryCategory $category
     * @return array
     */
    public function findAllByVisibilityAndStatusAndCategory(string $visibility, string $status, InventoryCategory $category): array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('LogisticsBundle\Entity\InventoryArticle', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('a.visibility'), ':visibility'),
                    $query->expr()->like($query->expr()->lower('a.status'), ':status'),
                    $query->expr()->eq('a.category', ':category'),
                )
            )
            ->setParameter('status', '%' . strtolower($status) . '%')
            ->setParameter('visibility', '%' . strtolower($visibility) . '%')
            ->setParameter('category', $category)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
