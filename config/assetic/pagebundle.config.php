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

return array(
    'controllers'  => array(
        'page_install' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
        ),

        'page_admin_page' => array(
            '@common_jquery',
            '@admin_js',
            '@bootstrap_js_tab',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@common_jquery_form',
            '@common_form_upload_progress',
            '@gollum_css',
            '@gollum_js',
            '@admin_css',
        ),
        'page_admin_category' => array(
            '@common_jquery',
            '@admin_js',
            '@bootstrap_js_tab',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@admin_css',
        ),
        'page_admin_link' => array(
            '@common_jquery',
            '@admin_js',
            '@bootstrap_js_tab',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@admin_css',
        ),

        'page' => array(
            '@common_jquery',
            '@bootstrap_css',
            '@site_css',
            '@flaty_css',
            '@bootstrap_responsive_css',
            '@bootstrap_js_dropdown',
            '@bootstrap_js_transition',
            '@bootstrap_js_carousel',
            '@bootstrap_js_collapse',
            '@bootstrap_js_alert',
        ),
    ),
    'routes' => array(),
);
