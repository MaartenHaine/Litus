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

namespace BrBundle;

use CommonBundle\Component\Module\Config;

return array(
    'router' => array(
        'routes' => array(
            'br_install' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/install/br[/]',
                    'defaults' => array(
                        'controller' => 'br_install',
                        'action'     => 'index',
                    ),
                ),
            ),
            'br_admin_company' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/company[/:action[/:id][/page/:page][/:field/:string]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[a-zA-Z0-9_-]*',
                        'page'    => '[0-9]*',
                        'field'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'string'       => '[a-zA-Z][%a-zA-Z0-9:.,_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_company',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_company_event' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/company/event[/:action[/:id]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_company_event',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_company_job' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/company/job[/:action[/:id]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_company_job',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_company_user' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/company/user[/:action[/:id][/page/:page]][/]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'page'   => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_company_user',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_company_logo' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/company/logos[/:action[/:id]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_company_logo',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_cv_entry' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/cv/entry[/:action[/:id][/page/:page][/:academicyear]][/]',
                    'constraints' => array(
                        'action'       => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'           => '[0-9]*',
                        'academicyear' => '[0-9]{4}-[0-9]{4}',
                        'page'         => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_cv_entry',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_contract' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/contract[/:action[/:id]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_contract',
                        'action'     => 'view',
                    ),
                ),
            ),
            'br_admin_invoice' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/invoice[/:action[/:id][/date/:date]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[0-9]*',
                        'date'    => '[0-9]{2}/[0-9]{2}/[0-9]{4}'
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_invoice',
                        'action'     => 'view',
                    ),
                ),
            ),
            'br_admin_order' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/order[/:action[/:id]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_order',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_admin_product' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/product[/:action[/:id]][/]',
                    'constraints' => array(
                        'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'      => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_admin_product',
                        'action'     => 'manage',
                    ),
                ),
            ),
            'br_career_index' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/career[/:action][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'language' => '[a-z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'br_career_company' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/career/company[/:action[/:company][/id/:id]][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'company'     => '[a-zA-Z0-9_-]*',
                        'language' => '[a-z]{2}',
                        'id'      => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_company',
                        'action'     => 'overview',
                    ),
                ),
            ),
            'br_career_company_search' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/career/company/search[/:string][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'string'   => '[%a-zA-Z0-9:.,_-]*',
                        'language' => '[a-z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_company',
                        'action'     => 'search',
                    ),
                ),
            ),
            'br_career_event' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/career/event[/:action[/:id][/page/:page]][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'       => '[0-9_-]*',
                        'language' => '[a-z]{2}',
                        'page'     => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_event',
                        'action'     => 'overview',
                    ),
                ),
            ),
            'br_career_vacancy' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/career/vacancy[/:action[/:id][/page/:page]][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'       => '[0-9_-]*',
                        'language' => '[a-z]{2}',
                        'page'     => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_vacancy',
                        'action'     => 'overview',
                    ),
                ),
            ),
            'br_career_internship' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/career/internship[/:action[/:id][/page/:page]][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'       => '[0-9_-]*',
                        'language' => '[a-z]{2}',
                        'page'     => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_internship',
                        'action'     => 'overview',
                    ),
                ),
            ),
            'br_career_file' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/career/company/file/:name[/]',
                    'constraints' => array(
                        'name'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_career_company',
                        'action'     => 'file',
                    ),
                ),
            ),
            'br_corporate_index' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/corporate[/:action][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'academicyear' => '[0-9]{4}-[0-9]{4}',
                        'language' => '[a-z]{2}',
                        'image'    => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_corporate_index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'br_corporate_cv' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/corporate/cv[/:action[/type/:type][/string/:string][/min/:min][/max/:max][/image/:image][/:academicyear]][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'academicyear' => '[0-9]{4}-[0-9]{4}',
                        'language' => '[a-z]{2}',
                        'image'    => '[a-zA-Z0-9]*',
                        'type'     => '[a-zA-Z]*',
                        'string'   => '[%a-zA-Z0-9:.,_-]*',
                        'min'      => '[0-9]*',
                        'max'      => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'br_corporate_cv',
                        'action'     => 'grouped',
                    ),
                ),
            ),
            'br_corporate_auth' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/corporate/auth[/:action][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'session'  => '[0-9]*',
                        'language' => '[a-z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'br_corporate_auth',
                        'action'     => 'login',
                    ),
                ),
            ),
            'br_cv_index' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '[/:language]/cv[/:action][/]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'language' => '[a-z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'br_cv_index',
                        'action'     => 'cv',
                    ),
                ),
            ),
        ),
    ),
    'translator' => array(
        'translation_files' => array(
            array(
                'type'     => 'phparray',
                'filename' => __DIR__ . '/../translations/corporate.en.php',
                'locale'   => 'en'
            ),
            array(
                'type'     => 'phparray',
                'filename' => __DIR__ . '/../translations/corporate.nl.php',
                'locale'   => 'nl'
            ),
            array(
                'type'     => 'phparray',
                'filename' => __DIR__ . '/../translations/career.en.php',
                'locale'   => 'en'
            ),
            array(
                'type'     => 'phparray',
                'filename' => __DIR__ . '/../translations/career.nl.php',
                'locale'   => 'nl'
            ),
            array(
                'type'     => 'phparray',
                'filename' => __DIR__ . '/../translations/cv.en.php',
                'locale'   => 'en'
            ),
            array(
                'type'     => 'phparray',
                'filename' => __DIR__ . '/../translations/cv.nl.php',
                'locale'   => 'nl'
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'br_layout' => __DIR__ . '/../layouts',
            'br_view' => __DIR__ . '/../views',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            'orm_default' => array(
                'drivers' => array(
                    'BrBundle\Entity' => 'orm_annotation_driver'
                ),
            ),
            'orm_annotation_driver' => array(
                'paths' => array(
                    'brbundle' => __DIR__ . '/../../Entity',
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'br_install'               => 'BrBundle\Controller\Admin\InstallController',

            'br_admin_company'         => 'BrBundle\Controller\Admin\CompanyController',
            'br_admin_company_event'   => 'BrBundle\Controller\Admin\Company\EventController',
            'br_admin_company_job'     => 'BrBundle\Controller\Admin\Company\JobController',
            'br_admin_company_user'    => 'BrBundle\Controller\Admin\Company\UserController',
            'br_admin_company_logo'    => 'BrBundle\Controller\Admin\Company\LogoController',
            'br_admin_contract'        => 'BrBundle\Controller\Admin\ContractController',
            'br_admin_cv_entry'        => 'BrBundle\Controller\Admin\CvController',
            'br_admin_invoice'         => 'BrBundle\Controller\Admin\InvoiceController',
            'br_admin_order'           => 'BrBundle\Controller\Admin\OrderController',
            'br_admin_product'         => 'BrBundle\Controller\Admin\ProductController',
            ),
    ),
);