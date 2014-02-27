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

namespace CudiBundle\Repository\Prof;

use CommonBundle\Entity\User\Person,
    CommonBundle\Component\Doctrine\ORM\EntityRepository;

/**
 * Action
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Action extends EntityRepository
{
    public function findAllUncompletedQuery($nbResults = null)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->isNull('a.refuseDate'),
                    $query->expr()->isNull('a.confirmDate')
                )
            )
            ->orderBy('a.timestamp', 'ASC')
            ->setMaxResults($nbResults)
            ->getQuery();

        return $resultSet;
    }

    public function findAllCompletedQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                   $query->expr()->isNotNull('a.confirmDate')
            )
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllRefusedQuery()
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                   $query->expr()->isNotNull('a.refuseDate')
            )
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByPersonQuery(Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->eq('a.person', ':person')
            )
            ->setParameter('person', $person->getId())
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByEntityAndActionAndPersonQuery($entity, $action, Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.person', ':person'),
                    $query->expr()->eq('a.entity', ':entity'),
                    $query->expr()->eq('a.action', ':action')
                )
            )
            ->setParameter('person', $person->getId())
            ->setParameter('entity', $entity)
            ->setParameter('action', $action)
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByEntityAndEntityIdAndActionAndPersonQuery($entity, $entityId, $action, Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.person', ':person'),
                    $query->expr()->eq('a.entity', ':entity'),
                    $query->expr()->eq('a.entityId', ':entityId'),
                    $query->expr()->eq('a.action', ':action')
                )
            )
            ->setParameter('person', $person->getId())
            ->setParameter('entity', $entity)
            ->setParameter('entityId', $entityId)
            ->setParameter('action', $action)
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByEntityAndEntityIdAndPersonQuery($entity, $entityId, Person $person)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.person', ':person'),
                    $query->expr()->eq('a.entity', ':entity'),
                    $query->expr()->eq('a.entityId', ':entityId')
                )
            )
            ->setParameter('person', $person->getId())
            ->setParameter('entity', $entity)
            ->setParameter('entityId', $entityId)
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByEntityAndPreviousIdAndActionQuery($entity, $previousId, $action)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.entity', ':entity'),
                    $query->expr()->eq('a.previousId', ':previousId'),
                    $query->expr()->eq('a.action', ':action')
                )
            )
            ->setParameter('entity', $entity)
            ->setParameter('previousId', $previousId)
            ->setParameter('action', $action)
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByEntityAndEntityIdAndActionQuery($entity, $entityId, $action)
    {
        $query = $this->_em->createQueryBuilder();
        $resultSet = $query->select('a')
            ->from('CudiBundle\Entity\Prof\Action', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('a.entity', ':entity'),
                    $query->expr()->eq('a.entityId', ':entityId'),
                    $query->expr()->eq('a.action', ':action')
                )
            )
            ->setParameter('entity', $entity)
            ->setParameter('entityId', $entityId)
            ->setParameter('action', $action)
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery();

        return $resultSet;
    }
}
