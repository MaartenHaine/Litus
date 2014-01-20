<?php

namespace SyllabusBundle\Repository\Subject;

use CommonBundle\Entity\General\AcademicYear,
    CommonBundle\Entity\User\Person,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Comment
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Comment extends EntityRepository
{
    public function findLast($nb = 10)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('SyllabusBundle\Entity\Subject\Comment', 'c')
            ->where(
                $query->expr()->isNull('c.readBy')
            )
            ->orderBy('c.date', 'DESC')
            ->setMaxResults($nb)
            ->getQuery()
            ->getResult();

        return $resultSet;
    }

    public function findAllByAcademicYearQuery(AcademicYear $academicYear)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('s.id')
            ->from('SyllabusBundle\Entity\StudySubjectMap', 'm')
            ->innerJoin('m.subject', 's')
            ->where(
                $query->expr()->eq('m.academicYear', ':academicYear')
            )
            ->setParameter('academicYear', $academicYear)
            ->getQuery()
            ->getResult();

        $ids = array(0 => 0);
        foreach($resultSet as $item)
            $ids[$item['id']] = $item['id'];

        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('SyllabusBundle\Entity\Subject\Comment', 'c')
            ->where(
                $query->expr()->in('c.subject', $ids)
            )
            ->orderBy('c.date', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findRecentConversationsByPersonAndAcademicYear(Person $person, AcademicYear $academicYear)
    {
        $subjects = $this->_em
            ->getRepository('SyllabusBundle\Entity\SubjectProfMap')
            ->findAllByProfAndAcademicYear($person, $academicYear);

        $comments = array();
        foreach($subjects as $subject) {
            $commentsOfSubject = $this->_em
                ->getRepository('SyllabusBundle\Entity\Subject\Comment')
                ->findBySubject($subject->getSubject());

            foreach($commentsOfSubject as $comment) {
                $reply = $this->_em
                    ->getRepository('SyllabusBundle\Entity\Subject\Reply')
                    ->findLastByComment($comment);

                if (null !== $reply)
                    $comments[$reply->getDate()->getTimestamp()] = array('type' => 'reply', 'content' => $reply);
                else
                    $comments[$comment->getDate()->getTimestamp()] = array('type' => 'comment', 'content' => $comment);
            }
        }

        ksort($comments);
        return array_slice($comments, 0, 5);
    }
}
