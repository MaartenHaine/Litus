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

namespace CudiBundle\Repository\Sale;

use CommonBundle\Entity\General\AcademicYear;
use CudiBundle\Entity\Article as ArticleEntity;
use CudiBundle\Entity\Supplier;

/**
 * Article
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Article extends \CommonBundle\Component\Doctrine\ORM\EntityRepository
{
    /**
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return \Doctrine\ORM\Query
     */
    public function findAllByAcademicYearQuery(AcademicYear $academicYear, $semester = 0)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, $semester);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  AcademicYear $academicYear
     * @return array
     */
    public function findAllByAcademicYearSortBarcode(AcademicYear $academicYear)
    {
        $articles = $this->getArticleIdsBySemester($academicYear);

        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->getQuery()
            ->getResult();

        $barcodes = array();
        foreach ($resultSet as $article) {
            $barcodes[] = $article->getBarcode();
        }

        array_multisort($barcodes, $resultSet);

        return $resultSet;
    }

    /**
     * @param  ArticleEntity $article
     * @return \CudiBundle\Entity\Sale\Article|null
     */
    public function findOneByArticle(ArticleEntity $article)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.mainArticle', ':article')
                )
            )
            ->setParameter('article', $article->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param  integer $barcode
     * @return \CudiBundle\Entity\Sale\Article|null
     */
    public function findOneByBarcode($barcode)
    {
        $barcode = $this->getEntityManager()
            ->getRepository('CudiBundle\Entity\Sale\Article\Barcode')
            ->findOneByBarcode($barcode);

        if (isset($barcode)) {
            return $barcode->getArticle();
        }

        return null;
    }

    /**
     * @param  string       $type
     * @param  AcademicYear $academicYear
     * @return \Doctrine\ORM\Query
     */
    public function findAllByTypeAndAcademicYearQuery($type, AcademicYear $academicYear)
    {
        $articles = $this->getArticleIdsBySemester($academicYear);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('m.type', ':type'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('type', $type)
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  string       $title
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return \Doctrine\ORM\Query
     */
    public function findAllByTitleAndAcademicYearQuery($title, AcademicYear $academicYear, $semester = 0)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, $semester);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.title'), ':title'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('title', '%' . strtolower($title) . '%')
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  string       $author
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return \Doctrine\ORM\Query
     */
    public function findAllByAuthorAndAcademicYearQuery($author, AcademicYear $academicYear, $semester = 0)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, $semester);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.authors'), ':author'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('author', '%' . strtolower($author) . '%')
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  string       $string
     * @param  AcademicYear $academicYear
     * @return \Doctrine\ORM\Query
     */
    public function findAllByTitleOrAuthorAndAcademicYearQuery($string, AcademicYear $academicYear)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, 0);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->orX(
                        $query->expr()->like($query->expr()->lower('m.title'), ':string'),
                        $query->expr()->like($query->expr()->lower('m.authors'), ':string')
                    ),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('string', '%' . strtolower($string) . '%')
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  string       $publisher
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return \Doctrine\ORM\Query
     */
    public function findAllByPublisherAndAcademicYearQuery($publisher, AcademicYear $academicYear, $semester = 0)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, $semester);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('m.publishers'), ':publisher'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('publisher', '%' . strtolower($publisher) . '%')
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  integer      $barcode
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return \Doctrine\ORM\Query
     */
    public function findAllByBarcodeAndAcademicYearQuery($barcode, AcademicYear $academicYear, $semester = 0)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, $semester);

        $query = $this->getEntityManager()->createQueryBuilder();
        $articles = $query->select('m.id')
            ->from('CudiBundle\Entity\Sale\Article\Barcode', 'b')
            ->innerJoin('b.article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->concat('b.barcode', '\'\''), ':barcode'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('barcode', '%' . $barcode . '%')
            ->getQuery()
            ->getResult();

        $ids = array(0);
        foreach ($articles as $id) {
            $ids[] = $id['id'];
        }

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $ids)
                )
            )
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  integer      $supplier
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return \Doctrine\ORM\Query
     */
    public function findAllBySupplierStringAndAcademicYearQuery($supplier, AcademicYear $academicYear, $semester = 0)
    {
        $articles = $this->getArticleIdsBySemester($academicYear, $semester);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->innerJoin('a.supplier', 's')
            ->where(
                $query->expr()->andX(
                    $query->expr()->like($query->expr()->lower('s.name'), ':supplier'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('supplier', '%' . strtolower($supplier) . '%')
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  Supplier $supplier
     * @return \Doctrine\ORM\Query
     */
    public function findAllBySupplierQuery(Supplier $supplier)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.supplier', ':supplier'),
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('m.isHistory', 'false'),
                    $query->expr()->eq('m.isProf', 'false')
                )
            )
            ->setParameter('supplier', $supplier->getId())
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  string       $title
     * @param  AcademicYear $academicYear
     * @return \Doctrine\ORM\Query
     */
    public function findAllByTitleOrBarcodeAndAcademicYearQuery($title, AcademicYear $academicYear)
    {
        $articles = $this->getArticleIdsBySemester($academicYear);

        $query = $this->getEntityManager()->createQueryBuilder();
        return $query->select('a, m')
            ->distinct()
            ->from('CudiBundle\Entity\Sale\Article', 'a')
            ->leftJoin('CudiBundle\Entity\Sale\Article\Barcode', 'b', 'WITH', 'b.article = a.id')
            ->innerJoin('a.mainArticle', 'm')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->orX(
                        $query->expr()->like($query->expr()->lower('m.title'), ':title'),
                        $query->expr()->like($query->expr()->concat('b.barcode', '\'\''), ':title')
                    ),
                    $query->expr()->in('m.id', $articles)
                )
            )
            ->setParameter('title', '%' . strtolower($title) . '%')
            ->orderBy('m.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param  AcademicYear $academicYear
     * @param  integer      $semester
     * @return array
     */
    private function getArticleIdsBySemester(AcademicYear $academicYear, $semester = 0)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        if ($semester == 0) {
            $resultSet = $query->select('a.id')
                ->from('CudiBundle\Entity\Article\SubjectMap', 'm')
                ->innerJoin('m.article', 'a')
                ->innerJoin('m.subject', 's')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('a.isHistory', 'false'),
                        $query->expr()->eq('a.isProf', 'false'),
                        $query->expr()->eq('m.academicYear', ':academicYear')
                    )
                )
                ->setParameter('academicYear', $academicYear->getId())
                ->getQuery()
                ->getResult();
        } else {
            $query = $this->getEntityManager()->createQueryBuilder();
            $resultSet = $query->select('a.id')
                ->from('CudiBundle\Entity\Article\SubjectMap', 'm')
                ->innerJoin('m.article', 'a')
                ->innerJoin('m.subject', 's')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq('a.isHistory', 'false'),
                        $query->expr()->eq('a.isProf', 'false'),
                        $query->expr()->orX(
                            $query->expr()->eq('s.semester', '0'),
                            $query->expr()->eq('s.semester', ':semester')
                        ),
                        $query->expr()->eq('m.academicYear', ':academicYear')
                    )
                )
                ->setParameter('semester', $semester)
                ->setParameter('academicYear', $academicYear->getId())
                ->getQuery()
                ->getResult();
        }

        $articles = array(0);
        foreach ($resultSet as $item) {
            $articles[$item['id']] = $item['id'];
        }

        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('a.id')
            ->from('CudiBundle\Entity\Article', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.isHistory', 'false'),
                    $query->expr()->eq('a.isProf', 'false'),
                    $query->expr()->eq('a.type', '\'common\'')
                )
            )
            ->getQuery()
            ->getResult();

        foreach ($resultSet as $item) {
            $articles[$item['id']] = $item['id'];
        }

        return $articles;
    }
}
