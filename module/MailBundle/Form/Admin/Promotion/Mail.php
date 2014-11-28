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

namespace MailBundle\Form\Admin\Promotion;

use CommonBundle\Component\Form\Admin\Element\File,
    CommonBundle\Component\Form\Admin\Element\Select,
    CommonBundle\Component\Form\Admin\Element\Text,
    CommonBundle\Component\Form\Admin\Element\Textarea,
    Doctrine\ORM\EntityManager,
    MailBundle\Component\Validator\MultiMail as MultiMailValidator,
    Zend\Form\Element\Submit,
    Zend\InputFilter\Factory as InputFactory,
    Zend\InputFilter\InputFilter;

/**
 * Send Mail
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class Mail extends \CommonBundle\Component\Form\Admin\Form
{
    /**
     * @var EntityManager The EntityManager instance
     */
    private $_entityManager = null;

    /**
     * @param EntityManager   $entityManager The EntityManager instance
     * @param null|string|int $name          Optional name for the element
     */
    public function __construct(EntityManager $entityManager, $groups, $name = null)
    {
        parent::__construct($name);

        $this->_entityManager = $entityManager;

        $groupNames = array();
        foreach ($groups as $group) {
            if (strpos($group->getName(), "Master") === 0) {
                $groupNames[$group->getId()] = $group->getName();
            }
        }

        $field = new Select('to');
        $field->setLabel('To')
            ->setAttribute('multiple', true)
            ->setAttribute('options', $this->_createPromotionsArray())
            ->setRequired();
        $this->add($field);

        if (0 != count($groupNames)) {
            $field = new Select('groups');
            $field->setLabel('Groups')
                ->setAttribute('multiple', true)
                ->setAttribute('options', $groupNames);
            $this->add($field);
        }

        $field = new Text('subject');
        $field->setLabel('Subject')
            ->setAttribute('style', 'width: 400px;')
            ->setRequired();
        $this->add($field);

        $field = new Text('bcc');
        $field->setLabel('Additional BCC')
            ->setAttribute('style', 'width: 400px;');
        $this->add($field);

        $field = new Textarea('message');
        $field->setLabel('Message')
            ->setAttribute('style', 'width: 500px; height: 200px;')
            ->setRequired();
        $this->add($field);

        $field = new File('file');
        $field->setLabel('Attachments')
            ->setAttribute('multiple', 'multiple')
            ->setRequired();
        $this->add($field);

        $field = new Submit('submit');
        $field->setValue('Send')
            ->setAttribute('class', 'mail');
        $this->add($field);
    }

    private function _createPromotionsArray()
    {
        $academicYears = $this->_entityManager
            ->getRepository('CommonBundle\Entity\General\AcademicYear')
            ->findAll();

        $promotionsArray = array();
        foreach ($academicYears as $academicYear) {
            $promotionsArray[$academicYear->getId()] = $academicYear->getCode();
        }

        return $promotionsArray;
    }

    public function getInputFilter()
    {
        $inputFilter = new InputFilter();
        $factory = new InputFactory();

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'to',
                    'required' => true,
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'subject',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'bcc',
                    'required' => false,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        new MultiMailValidator(),
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'message',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'file',
                    'required' => false,
                    'validators' => array(
                        array(
                            'name' => 'filefilessize',
                            'options' => array(
                                'max' => '50MB',
                            ),
                        ),
                    ),
                )
            )
        );

        return $inputFilter;
    }
}
