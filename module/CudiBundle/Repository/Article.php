<?php

namespace CudiBundle\Repository;

use CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Entity\User\Person,
    CommonBundle\Component\Util\EntityRepository;

/**
 * Article
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Article extends EntityRepository
{
    public function findAll()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false')
                )
            )
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByTitle($title)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where($query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('a.title'), ':title'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false')
                )
            )
            ->setParameter('title', '%'.strtolower($title).'%')
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByAuthor($author)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('a.authors'), ':author'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false')
                )
            )
            ->setParameter('author', '%'.strtolower($author).'%')
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByISBN($isbn)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where($query->expr()->andX(
                    $query->expr()->like($query->expr()->concat('a.isbn', '\'\''), ':isbn'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false')
                )
            )
            ->setParameter('isbn', '%'.strtolower($isbn).'%')
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByPublisher($publisher)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('a.publishers'), ':publisher'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false')
                )
            )
            ->setParameter('publisher', '%'.strtolower($publisher).'%')
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllBySubject($subject, AcademicYear $academicYear)
    {
        $query = $this->_em->createQueryBuilder();
        $subjects = $query->select('s')
            ->from('SyllabusBundle\Entity\Subject', 's')
            ->where(
                $query->expr()->orX(
                    $query->expr()->like($query->expr()->lower('s.name'), ':name'),
                    $query->expr()->like($query->expr()->lower('s.code'), ':name')
                )
            )
            ->setParameter('name', strtolower(trim($subject)) . '%')
            ->getQuery()
            ->getResult();

        $ids = array(0);
        foreach($subjects as $subject)
            $ids[] = $subject->getId();

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s')
            ->from('CudiBundle\Entity\Article\SubjectMap', 's')
            ->join('s.article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('s.subject', $ids),
                    $query->expr()->eq('s.academicYear', ':academicYear'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false')
                )
            )
            ->setParameter('academicYear', $academicYear)
            ->getQuery()
            ->getResult();

        $articles = array();
        foreach($resultSet as $mapping)
            $articles[] = $mapping->getArticle();

        return $articles;
    }

    public function findAllByProf(Person $person)
    {
        $subjects = $this->getEntityManager()
            ->getRepository('SyllabusBundle\Entity\SubjectProfMap')
            ->findByProf($person);

        $ids = array(0);
        foreach($subjects as $subject)
            $ids[] = $subject->getSubject()->getId();

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('m')
            ->from('CudiBundle\Entity\Article\SubjectMap', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('m.removed', 'false'),
                    $query->expr()->in('m.subject', $ids)
                )
            )
            ->getQuery()
            ->getResult();

        $ids = array(0);
        foreach($resultSet as $mapping) {
            $edited = $this->getEntityManager()
                ->getRepository('CudiBundle\Entity\Prof\Action')
                ->findAllByEntityAndPreviousIdAndAction('article', $mapping->getArticle()->getId(), 'edit');

            if (isset($edited[0]) && !$edited[0]->isRefused()) {
                $ids[] = $edited[0]->getEntityId();
            } else {
                $ids[] = $mapping->getArticle()->getId();
            }
        }

        $added = $this->getEntityManager()
            ->getRepository('CudiBundle\Entity\Prof\Action')
            ->findAllByEntityAndActionAndPerson('article', 'add', $person);

        foreach($added as $add) {
            $edited = $this->getEntityManager()
                ->getRepository('CudiBundle\Entity\Prof\Action')
                ->findAllByEntityAndPreviousIdAndAction('article', $add->getEntityId(), 'edit');

            if (isset($edited[0]) && !$edited[0]->isRefused()) {
                $ids[] = $edited[0]->getEntityId();
            } else {
                $ids[] = $add->getEntityId();
            }
        }

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('a.id', $ids)
                )
            )
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult();

        $articles = array();
        foreach($resultSet as $article) {
            if (!$article->isInternal() || $article->isOfficial())
                $articles[] = $article;
        }

        return $articles;
    }

    public function findOneByIdAndProf($id, Person $person)
    {
        $subjects = $this->getEntityManager()
            ->getRepository('SyllabusBundle\Entity\SubjectProfMap')
            ->findByProf($person);

        $ids = array(0);
        foreach($subjects as $subject)
            $ids[] = $subject->getSubject()->getId();

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('m')
            ->from('CudiBundle\Entity\Article\SubjectMap', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('m.removed', 'false'),
                    $query->expr()->eq('m.article', ':id'),
                    $query->expr()->in('m.subject', $ids)
                )
            )
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (isset($resultSet[0]) &&
                (!$resultSet[0]->getArticle()->isInternal() || $resultSet[0]->getArticle()->isOfficial()))
            return $resultSet[0]->getArticle();

        $actions = $this->getEntityManager()
            ->getRepository('CudiBundle\Entity\Prof\Action')
            ->findAllByEntityAndEntityIdAndPerson('article', $id, $person);

        if (isset($actions[0]))
            return $actions[0]->setEntityManager($this->_em)
                ->getEntity();

        return null;
    }
}
