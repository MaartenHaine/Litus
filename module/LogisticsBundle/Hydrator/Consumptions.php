<?php

namespace LogisticsBundle\Hydrator;

use LogisticsBundle\Entity\Consumptions as ConsumptionsEntity;

class Consumptions extends \CommonBundle\Component\Hydrator\Hydrator
{
    protected function doHydrate(array $array, $object = null)
    {
        if ($object === null) {
            $object = new ConsumptionsEntity();
        }

       $academic = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\User\Person')
            ->findOneById($array['person']['id']);
        $object->setAcademic($academic);

        $numberOfConsumptions = $array['number_of_consumptions'];
        $object->setConsumptions($numberOfConsumptions);
        $object->setUserName($academic->getUserName());
        $object->setFullName($academic->getFullName());

        return $this->stdHydrate($array, $object);
    }

    protected function doExtract($object = null)
    {
        if ($object === null) {
            return array();
        }

        $data = $this->stdExtract($object);

        $data['academic'] = $object->getAcademic() !== null ? $object->getAcademic() : -1;
        $data['numberOfConsumptions'] = $object->getConsumptions();

        return $data;
    }
}