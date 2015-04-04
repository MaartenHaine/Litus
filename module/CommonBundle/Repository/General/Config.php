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

namespace CommonBundle\Repository\General;

use CommonBundle\Component\Doctrine\ORM\EntityRepository,
    RuntimeException;

/**
 * Config
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Config extends EntityRepository
{
    public function findAllQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $resultSet = $query->select('c')
            ->from('CommonBundle\Entity\General\Config', 'c')
            ->orderBy('c.key', 'ASC')
            ->getQuery();

        return $resultSet;
    }

    public function findAllByPrefix($prefix)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $configs = $query->select('c')
            ->from('CommonBundle\Entity\General\Config', 'c')
            ->where(
                $query->expr()->like('c.key', ':prefix')
            )
            ->setParameter('prefix', $prefix . '.%')
            ->getQuery()
            ->getResult();

        $result = array();
        foreach ($configs as $config) {
            $key = $config->getKey();
            $value = $config->getValue();

            $key = str_replace($prefix . '.','', $key);

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param string $key
     */
    public function getConfigValue($key)
    {
        $config = $this->find($key);

        if ($config === null) {
            throw new RuntimeException('Configuration entry ' . $key . ' not found');
        }

        return $config->getValue();
    }
}
