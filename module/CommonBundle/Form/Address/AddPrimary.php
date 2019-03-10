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

namespace CommonBundle\Form\Address;

/**
 * Add Address
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class AddPrimary extends \CommonBundle\Component\Form\Fieldset
{
    public function init()
    {
        parent::init();

        $this->addClass('primary_address');
        $this->setLabelOption('disable_html_escape', true);

        list($cities, $streets) = $this->getCities();

        $this->add(
            array(
                'type'       => 'select',
                'name'       => 'city',
                'label'      => 'City',
                'attributes' => array(
                    'options' => $cities,
                    'class'   => 'city',
                ),
                'options' => array(
                    'input' => array(
                        'input' => array(
                            'filters' => array(
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'NotEmpty',
                                'options' => array(
                                    'zero', 'string',
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );

        $this->add(
            array(
                'type'       => 'fieldset',
                'name'       => 'other',
                'attributes' => array(
                    'class' => 'other',
                ),
                'elements' => array(
                    array(
                        'type'    => 'text',
                        'name'    => 'postal',
                        'label'   => 'Postal Code',
                        'options' => array(
                            'input' => array(
                                'filters' => array(
                                    array('name' => 'StringTrim'),
                                ),
                            ),
                            'validators' => array(
                                array(
                                    'name'    => 'Alnum',
                                    'options' => array(
                                        'allowWhiteSpace' => true,
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'name'     => 'city',
                        'label'    => 'City',
                        'required' => true,
                        'options'  => array(
                            'input' => array(
                                'filters' => array(
                                    array('name' => 'StringTrim'),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'text',
                        'name'    => 'street',
                        'label'   => 'Street',
                        'options' => array(
                            'input' => array(
                                'filters' => array(
                                    array('name' => 'StringTrim'),
                                ),
                                'validators' => array(
                                    array('name' => 'NotEmpty'),
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );

        $streetSelects = array();
        foreach ($streets as $id => $collection) {
            $streetSelects[] = array(
                'type'       => 'select',
                'name'       => 'street_' . $id,
                'label'      => 'Street',
                'attributes' => array(
                    'class'   => 'street street-' . $id,
                    'options' => $collection,
                ),
                'options' => array(
                    'input' => array(
                        'filters' => array(
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'NotEmpty',
                                'options' => array(
                                    'zero', 'string',
                                ),
                            ),
                        ),
                    ),
                ),
            );
        }

        $this->add(
            array(
                'type'     => 'fieldset',
                'name'     => 'street',
                'elements' => $streetSelects,
            )
        );

        $this->add(
            array(
                'type'       => 'text',
                'name'       => 'number',
                'label'      => 'Number',
                'attributes' => array(
                    'class' => 'number',
                ),
                'options' => array(
                    'input' => array(
                        'filters' => array(
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'Alnum',
                                'options' => array(
                                    'allowWhiteSpace' => true,
                                ),
                            ),
                            array('name' => 'NotZero'),
                        ),
                    ),
                ),
            )
        );

        $this->add(
            array(
                'type'       => 'text',
                'name'       => 'mailbox',
                'label'      => 'Mailbox',
                'attributes' => array(
                    'class' => 'mailbox',
                ),
                'options' => array(
                    'input' => array(
                        'filters' => array(
                            array('name' => 'StringTrim'),
                        ),
                    ),
                ),
            )
        );
    }

    public function setAttribute($name, $value)
    {
        if ($name == 'required') {
            $this->get('street')->setAttribute('required', $value);
            $this->get('number')->setAttribute('required', $value);
            $this->get('mailbox')->setAttribute('required', $value);
            $this->get('city')->setAttribute('required', $value);
            $this->get('other')->setAttribute('required', $value);
        }

        return parent::setAttribute($name, $value);
    }

    private function getCities()
    {
        if ($this->getCache() !== null) {
            $result = $this->getCache()->getItem('Litus_CommonBundle_Streets');
            if ($result !== null) {
                return $result;
            }
        }

        $cities = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Address\City')
            ->findAll();

        $optionsCity = array('' => '');
        $optionsStreet = array();
        foreach ($cities as $city) {
            $optionsCity[$city->getId()] = $city->getPostal() . ' ' . $city->getName();
            $optionsStreet[$city->getId()] = array('' => '');

            foreach ($city->getStreets() as $street) {
                $optionsStreet[$city->getId()][$street->getId()] = $street->getName();
            }
        }

        $optionsCity['other'] = 'Other';

        if ($this->getCache() !== null) {
            $this->getCache()->setItem(
                'Litus_CommonBundle_Streets',
                array(
                    $optionsCity,
                    $optionsStreet,
                )
            );
        }

        return array($optionsCity, $optionsStreet);
    }

    public function getInputFilterSpecification()
    {
        $specs = parent::getInputFilterSpecification();

        $required = false;
        if ($this->hasAttribute('required')) {
            $required = $this->getAttribute('required');
        }

        if ($this->get('city')->getValue() == '' && !$required) {
            return array();
        }

        if ($this->get('city')->getValue() != 'other') {
            unset($specs['other']);

            if (is_array($specs['street'])) {
                foreach (array_keys($specs['street']) as $city) {
                    if ($city == 'type') {
                        continue;
                    }

                    $specs['street'][$city]['required'] = $required && ($city == 'street_' . $this->get('city')->getValue());
                }
            }
        } else {
            unset($specs['street']);
        }

        return $specs;
    }
}
