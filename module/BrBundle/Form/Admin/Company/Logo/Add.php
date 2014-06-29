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

namespace BrBundle\Form\Admin\Company\Logo;

use BrBundle\Component\Validator\Logo\Type as TypeValidator,
    BrBundle\Entity\Company,
    BrBundle\Entity\Company\Logo,
    CommonBundle\Component\OldForm\Admin\Element\Select,
    CommonBundle\Component\OldForm\Admin\Element\Text,
    CommonBundle\Component\OldForm\Admin\Element\File,
    Doctrine\ORM\EntityManager,
    Zend\InputFilter\InputFilter,
    Zend\InputFilter\Factory as InputFactory,
    Zend\Form\Element\Submit;

/**
 * Add Logo
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class Add extends \CommonBundle\Component\OldForm\Admin\Form
{

    const FILESIZE = '10MB';

    /**
     * @var \Doctrine\ORM\EntityManager The Doctrine EntityManager
     */
    private $_entityManager;

    /**
     * @var \BrBundle\Entity\Company The company to add the logo
     */
    private $_company;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager The EntityManager instance
     * @param \BrBundle\Entity\Company The company to add the logo
     * @param null|string|int             $name          Optional name for the element
     */
    public function __construct(EntityManager $entityManager, Company $company, $name = null)
    {
        parent::__construct($name);

        $this->_entityManager = $entityManager;
        $this->_company = $company;

        $this->setAttribute('enctype', 'multipart/form-data');

        $field = new File('logo');
        $field->setLabel('Logo')
            ->setAttribute('data-help', 'The logo must be an image of max ' . self::FILESIZE . '.')
            ->setRequired();
        $this->add($field);

        $field = new Text('url');
        $field->setLabel('URL')
            ->setRequired();
        $this->add($field);

        $field = new Select('type');
        $field->setLabel('Type')
            ->setAttribute('options', Logo::$POSSIBLE_TYPES)
            ->setAttribute('data-help', 'The location where the logo will be used:
            <ul>
                <li><b>Homepage:</b> In the footer of the website</li>
                <li><b>Cudi:<br> In the footer of the queue screen at Cudi</li>
            </ul>')
            ->setRequired();
        $this->add($field);

        $field = new Submit('submit');
        $field->setValue('Add')
            ->setAttribute('class', 'logo_add');
        $this->add($field);
    }

    public function getInputFilter()
    {
        $inputFilter = new InputFilter();
        $factory = new InputFactory();

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'logo',
                    'required' => false,
                    'validators' => array(
                        array(
                            'name' => 'fileisimage',
                        ),
                        array(
                            'name' => 'filefilessize',
                            'options' => array(
                                'max' => self::FILESIZE,
                            ),
                        ),
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'type',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        new TypeValidator($this->_entityManager, $this->_company),
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'url',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'uri',
                        )
                    ),
                )
            )
        );

        return $inputFilter;
    }
}
