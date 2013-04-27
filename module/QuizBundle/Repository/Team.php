<?php

namespace QuizBundle\Repository;

use QuizBundle\Entity\Quiz as QuizEntity,
    Doctrine\ORM\EntityRepository;

/**
 * Team
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Team extends EntityRepository
{
    /**
     * Gets all teams belonging to a quiz
     * @param QuizBundle\Entity\Quiz $quiz The team the rounds must belong to
     */
    public function findByQuiz(QuizEntity $quiz)
    {
        $query = $this->_em->createQueryBuilder();

        return $query->select('team')
            ->from('QuizBundle\Entity\Team', 'team')
            ->where(
                $query->expr()->eq('team.quiz', ':quiz')
            )
            ->orderBy('team.number', 'ASC')
            ->setParameter('quiz', $quiz->getId())
            ->getQuery()
            ->getResult();
    }
}
