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
        'syllabus_install' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
        ),
        'syllabus_admin_update' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@common_socket',
            '@common_permanent_modal',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
        ),
        'syllabus_admin_academic' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@common_typeahead_remote',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
        ),
        'syllabus_admin_group' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@common_typeahead_remote',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
        ),
        'syllabus_admin_study' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
        ),
        'syllabus_admin_subject' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
        ),
        'syllabus_admin_subject_comment' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
        ),
        'syllabus_admin_prof' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@common_typeahead_remote',
        ),
    ),
    'routes' => array(),
);
