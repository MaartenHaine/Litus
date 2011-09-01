<?php

namespace Admin\Form\Company;

use \Litus\Form\Decorator\ButtonDecorator;
use \Litus\Form\Decorator\FieldDecorator;

use \Zend\Form\Form;
use \Zend\Form\Element\Submit;
use \Zend\Form\Element\Text;

class Add extends \Admin\Form\User\Add
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setAction('/admin/company/add');

        $this->removeElement('roles');
        $this->removeElement('submit');

        $this->addDisplayGroup(
            array(
                'username',
                'credential',
                'verify_credential',
                'first_name',
                'last_name',
                'email'
            ),
            'contact_information'
        );
        $this->getDisplayGroup('contact_information')
            ->setLegend('Contact Information')
            ->setAttrib('id', 'contact_information')
            ->removeDecorator('DtDdWrapper');

        $field = new Text('company_name');
        $field->setLabel('Company Name')
                ->setRequired()
                ->setDecorators(array(new FieldDecorator()));
        $this->addElement($field);

        $field = new Text('vat_number');
        $field->setLabel('VAT Number')
                ->setRequired()
                ->setDecorators(array(new FieldDecorator()));
        $this->addElement($field);

        $this->addDisplayGroup(
            array(
                'company_name',
                'vat_number',
            ),
            'company_information'
        );
        $this->getDisplayGroup('company_information')
            ->setLegend('Company Information')
            ->setAttrib('id', 'company_information')
            ->removeDecorator('DtDdWrapper');

        $field = new Submit('submit');
        $field->setLabel('Add')
                ->setAttrib('class', 'companies_add')
                ->setDecorators(array(new ButtonDecorator()));
        $this->addElement($field);
    }
}