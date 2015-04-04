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

namespace CudiBundle\Repository\Sale;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Entity\General\Organization,
    CudiBundle\Entity\Sale\Article as ArticleEntity,
    CudiBundle\Entity\Sale\Session as SessionEntity;

/**
 * ReturnItem
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReturnItem extends EntityRepository
{
    public function findNumberBySession(SessionEntity $session)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('COUNT(i.id)')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->where(
                $query->expr()->eq('i.session', ':session')
            )
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();

        if (null == $resultSet) {
            return 0;
        }

        return $resultSet;
    }

    public function findNumberByArticleAndAcademicYear(ArticleEntity $article, AcademicYear $academicYear, Organization $organization = null)
    {
        if (null !== $organization) {
            $ids = $this->personsByAcademicYearAndOrganization($academicYear, $organization);

            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('COUNT(i.id)')
                ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
                ->innerJoin('i.queueItem', 'q')
                ->innerJoin('i.session', 's')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('i.article', ':article'),
                        $query->expr()->gt('s.openDate', ':start'),
                        $query->expr()->lt('s.openDate', ':end'),
                        $query->expr()->in('q.person', $ids)
                    )
                )
                ->setParameter('article', $article)
                ->setParameter('start', $academicYear->getStartDate())
                ->setParameter('end', $academicYear->getEndDate())
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('COUNT(i.id)')
                ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
                ->innerJoin('i.session', 's')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('i.article', ':article'),
                        $query->expr()->gt('s.openDate', ':start'),
                        $query->expr()->lt('s.openDate', ':end')
                    )
                )
                ->setParameter('article', $article)
                ->setParameter('start', $academicYear->getStartDate())
                ->setParameter('end', $academicYear->getEndDate())
                ->getQuery()
                ->getSingleScalarResult();
        }

        if (null == $resultSet) {
            return 0;
        }

        return $resultSet;
    }

    public function findAllByAcademicYearQuery(AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->gt('s.openDate', ':start'),
                    $query->expr()->lt('s.openDate', ':end')
                )
            )
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByArticleAndAcademicYearQuery($article, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->innerJoin('i.session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.title'), ':article'),
                    $query->expr()->andX(
                        $query->expr()->gt('s.openDate', ':start'),
                        $query->expr()->lt('s.openDate', ':end')
                    )
                )
            )
            ->setParameter('article', '%' . strtolower($article) . '%')
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByPersonAndAcademicYearQuery($name, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.queueItem', 'q')
            ->innerJoin('q.person', 'p')
            ->innerJoin('i.session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orX(
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.firstName', "' '")),
                                $query->expr()->lower('p.lastName')
                            ),
                            ':name'
                        ),
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.lastName', "' '")),
                                $query->expr()->lower('p.firstName')
                            ),
                            ':name'
                        )
                    ),
                    $query->expr()->andX(
                        $query->expr()->gt('s.openDate', ':start'),
                        $query->expr()->lt('s.openDate', ':end')
                    )
                )
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByOrganizationAndAcademicYearQuery(Organization $organization = null, AcademicYear $academicYear)
    {
        $ids = $this->personsByAcademicYearAndOrganization($academicYear, $organization);

        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.queueItem', 'q')
            ->where(
                $organization == null ? $query->expr()->notIn('q.person', $ids) : $query->expr()->in('q.person', $ids)
            )
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllBySessionQuery(SessionEntity $session)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->where(
                $query->expr()->eq('i.session', ':session')
            )
            ->setParameter('session', $session)
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByArticleAndSessionQuery($article, SessionEntity $session)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.session', ':session'),
                    $query->expr()->like($query->expr()->lower('m.title'), ':article')
                )
            )
            ->setParameter('article', '%' . strtolower($article) . '%')
            ->setParameter('session', $session)
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByPersonAndSessionQuery($name, SessionEntity $session)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.queueItem', 'q')
            ->innerJoin('q.person', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.session', ':session'),
                    $query->expr()->orX(
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.firstName', "' '")),
                                $query->expr()->lower('p.lastName')
                            ),
                            ':name'
                        ),
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.lastName', "' '")),
                                $query->expr()->lower('p.firstName')
                            ),
                            ':name'
                        )
                    )
                )
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->setParameter('session', $session)
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByOrganizationAndSessionQuery(Organization $organization = null, SessionEntity $session)
    {
        $ids = $this->personsByAcademicYearAndOrganization($session->getAcademicYear(), $organization);

        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.queueItem', 'q')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.session', ':session'),
                    $organization == null ? $query->expr()->notIn('q.person', $ids) : $query->expr()->in('q.person', $ids)
                )
            )
            ->setParameter('session', $session)
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByArticleEntityQuery(ArticleEntity $article, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.article', ':article'),
                    $query->expr()->andX(
                        $query->expr()->gt('s.openDate', ':start'),
                        $query->expr()->lt('s.openDate', ':end')
                    )
                )
            )
            ->setParameter('article', $article)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByPersonAndArticleQuery($name, ArticleEntity $article, AcademicYear $academicYear)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.queueItem', 'q')
            ->innerJoin('q.person', 'p')
            ->innerJoin('i.session', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('i.article', ':article'),
                    $query->expr()->orX(
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.firstName', "' '")),
                                $query->expr()->lower('p.lastName')
                            ),
                            ':name'
                        ),
                        $query->expr()->like(
                            $query->expr()->concat(
                                $query->expr()->lower($query->expr()->concat('p.lastName', "' '")),
                                $query->expr()->lower('p.firstName')
                            ),
                            ':name'
                        )
                    ),
                    $query->expr()->andX(
                        $query->expr()->gt('s.openDate', ':start'),
                        $query->expr()->lt('s.openDate', ':end')
                    )
                )
            )
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->setParameter('article', $article)
            ->setParameter('start', $academicYear->getStartDate())
            ->setParameter('end', $academicYear->getEndDate())
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByOrganizationAndArticleQuery(Organization $organization = null, ArticleEntity $article, AcademicYear $academicYear)
    {
        $ids = $this->personsByAcademicYearAndOrganization($academicYear, $organization);

        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('i')
            ->from('CudiBundle\Entity\Sale\ReturnItem', 'i')
            ->innerJoin('i.queueItem', 'q')
            ->where(
                $query->expr()->andX(
                    $organization == null ? $query->expr()->notIn('q.person', $ids) : $query->expr()->in('q.person', $ids),
                    $query->expr()->eq('i.article', ':article')
                )
            )
            ->setParameter('article', $article)
            ->orderBy('i.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    private function personsByAcademicYearAndOrganization(AcademicYear $academicYear, Organization $organization = null)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('p.id')
            ->from('CommonBundle\Entity\User\Person\Organization\AcademicYearMap', 'm')
            ->innerJoin('m.academic', 'p')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('m.academicYear', ':academicYear'),
                    null === $organization ? '1=1' : $query->expr()->eq('m.organization', $organization->getId())
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->getQuery()
            ->getResult();

        $ids = array(0);
        foreach ($resultSet as $item) {
            $ids[] = $item['id'];
        }

        return $ids;
    }
}
