<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace CommonBundle\Form\Admin\Person;

use CommonBundle\Component\Form\Admin\Element\Select,
    CommonBundle\Component\Form\Admin\Element\Text,
    CommonBundle\Entity\User\Person,
    Doctrine\ORM\EntityManager,
    Zend\InputFilter\InputFilter,
    Zend\InputFilter\Factory as InputFactory;

/**
 * Edit Person
 *
 * @author Pieter Maene <pieter.maene@litus.cc>
 */
abstract class Edit extends \CommonBundle\Form\Admin\Person\Add
{
    /**
     * @param \Doctrine\ORM\EntityManager $entityManager The EntityManager instance
     * @param \CommonBundle\Entity\User\Person $person The person we're going to modify
     * @param null|string|int $name Optional name for the element
     */
    public function __construct(EntityManager $entityManager, Person $person, $name = null)
    {
        parent::__construct($entityManager, $name);

        $this->remove('username');

        $field = new Select('system_roles');
        $field->setLabel('System Groups')
            ->setAttribute('multiple', true)
            ->setAttribute('options', $this->_createSystemRolesArray())
            ->setAttribute('disabled', 'disabled');
        $this->add($field);

        $field = new Select('unit_roles');
        $field->setLabel('Unit Groups')
            ->setAttribute('multiple', true)
            ->setAttribute('options', $this->createRolesArray())
            ->setAttribute('disabled', 'disabled');
        $this->add($field);

        $field = new Text('code');
        $field->setLabel('Code')
            ->setAttribute('disabled', 'disabled');
        $this->add($field);

        $this->_populateFromPerson($person);
    }

    private function _populateFromPerson(Person $person)
    {
        $data = array(
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'email' => $person->getEmail(),
            'telephone' => $person->getPhonenumber(),
            'sex' => $person->getSex(),
            'roles' => $this->_createRolesPopulationArray($person->getRoles(false)),
            'system_roles' => $this->_createSystemRolesPopulationArray($person->getFlattenedRoles()),
            'unit_roles' => $this->_createRolesPopulationArray($person->getUnitRoles()),
            'code' => $person->getCode() ? $person->getCode()->getCode() : '',
        );

        $this->setData($data);
    }

    /**
     * Returns an array that is in the right format to populate the roles field.
     *
     * @param array $roles The user's roles
     * @return array
     */
    private function _createRolesPopulationArray(array $roles)
    {
        $rolesArray = array();
        foreach ($roles as $role) {
            if ($role->getSystem())
                continue;

            $rolesArray[] = $role->getName();
        }
        return $rolesArray;
    }

    /**
     * Returns an array that is in the right format to populate the roles field.
     *
     * @param array $toles The user's roles
     * @return array
     */
    private function _createSystemRolesPopulationArray(array $roles)
    {
        $rolesArray = array();
        foreach ($roles as $role) {
            if (!$role->getSystem())
                continue;

            $rolesArray[] = $role->getName();
        }
        return $rolesArray;
    }

    /**
     * Returns an array that has all the roles, so that they are available in the
     * roles multiselect.
     *
     * @return array
     */
    private function _createSystemRolesArray()
    {
        $roles = $this->_entityManager
            ->getRepository('CommonBundle\Entity\Acl\Role')
            ->findAll();

        $rolesArray = array();
        foreach ($roles as $role) {
            if (!$role->getSystem())
                continue;

            $rolesArray[$role->getName()] = $role->getName();
        }
        return $rolesArray;
    }

    public function getInputFilter()
    {
        $inputFilter = parent::getInputFilter();

        $inputFilter->remove('username');

        return $inputFilter;
    }
}
