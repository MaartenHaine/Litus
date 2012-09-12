<?php
/**
 * Litus is a project by a group of students from the K.U.Leuven. The goal is to create
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

namespace BrBundle\Controller\Admin;

use Exception;

/**
 * InstallController
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class InstallController extends \CommonBundle\Component\Controller\ActionController\InstallController
{
    protected function initConfig()
    {
        $this->installConfig(
            array(
                array(
                    'key'         => 'br.logo_path',
                    'value'       => 'data/br/companies',
                    'description' => 'The path to the company logo files',
                ),
            )
        );
    }

    protected function initAcl()
    {
        $this->installAcl(
            array(
                'brbundle' => array(
                    'admin_company' => array(
                        'add', 'delete', 'editLogo', 'edit', 'logo', 'manage'
                    ),
                    'admin_company_event' => array(
                        'add', 'delete', 'edit', 'editPoster', 'manage'
                    ),
                    'admin_company_intership' => array(
                        'add', 'delete', 'edit', 'manage'
                    ),
                    'admin_company_user' => array(
                        'add', 'delete', 'edit', 'manage'
                    ),
                    'admin_company_vacancy' => array(
                        'add', 'delete', 'edit', 'manage'
                    ),
                )
            )
        );

        $this->installRoles(
            array(
                'corporate' => array(
                    'system' => true,
                    'parents' => array(
                        'guest',
                    ),
                    'actions' => array(
                    ),
                ),
            )
        );
    }
}
