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

namespace LogisticsBundle;

use CommonBundle\Component\Assetic\Filter\Css as CssFilter;
use CommonBundle\Component\Assetic\Filter\Js as JsFilter;
use CommonBundle\Component\Assetic\Filter\Less as LessFilter;

return array(
    'controllers' => array(
        'logistics_admin_driver' => array(
            '@common_jquery',
            '@common_remote_typeahead',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@minicolor_css',
            '@minicolor_js',
        ),
        'logistics_admin_article' => array(
            '@common_jquery',
            '@common_remote_typeahead',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@minicolor_css',
            '@minicolor_js',
        ),
        'logistics_admin_order' => array(
            '@common_jquery',
            '@common_remote_typeahead',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@minicolor_css',
            '@minicolor_js',
            '@common_jqueryui',
            '@common_jqueryui_datepicker',
            '@common_jqueryui_css',
            '@common_jqueryui_datepicker_css',
        ),
        'logistics_admin_request' => array(
            '@common_jquery',
            '@common_remote_typeahead',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@minicolor_css',
            '@minicolor_js',
        ),
        'logistics_admin_van_reservation' => array(
            '@common_jquery',
            '@common_remote_typeahead',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@common_jqueryui',
            '@common_jqueryui_datepicker',
            '@common_jqueryui_css',
            '@common_jqueryui_datepicker_css',
        ),
        'logistics_admin_lease' => array(
            '@common_jquery',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
        ),
        'logistics_admin_piano_reservation' => array(
            '@common_jquery',
            '@common_remote_typeahead',
            '@admin_css',
            '@admin_js',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@common_jqueryui',
            '@common_jqueryui_datepicker',
            '@common_jqueryui_css',
            '@common_jqueryui_datepicker_css',
        ),
        'logistics_index' => array(
            '@common_jquery',
            '@common_jqueryui',
            '@common_remote_typeahead',
            '@common_jquery_form',
            '@fullcalendar_css',
            '@logistics_js',
            '@display_form_error_js',
            '@bootstrap_css',
            '@bootstrap_js_dropdown',
            '@bootstrap_js_alert',
            '@bootstrap_js_tooltip',
            '@bootstrap_js_popover',
            '@logistics_css',
        ),
        'logistics_lease' => array(
            '@common_jquery',
            '@common_jqueryui',
            '@common_remote_typeahead',
            '@bootstrap_css',
            '@bootstrap_js_dropdown',
            '@bootstrap_js_alert',
            '@logistics_css',
        ),
        'logistics_piano' => array(
            '@common_jquery',
            '@bootstrap_css',
            '@site_css',
            '@bootstrap_js_dropdown',
            '@bootstrap_js_transition',
            '@bootstrap_js_modal',
            '@bootstrap_js_carousel',
            '@bootstrap_js_tooltip',
            '@bootstrap_js_popover',
            '@bootstrap_js_collapse',
            '@bootstrap_js_alert',
            '@bootstrap_js_tab',
        ),
        'logistics_catalog' => array(
            '@common_jquery',
            '@common_jqueryui',
            '@common_remote_typeahead',
            '@common_jquery_form',
            '@display_form_error_js',
            '@bootstrap_css',
            '@bootstrap_js_dropdown',
            '@bootstrap_js_alert',
            '@bootstrap_js_tooltip',
            '@bootstrap_js_popover',
            '@logistics_css',
        ),
    ),

    'collections' => array(
        'logistics_css' => array(
            'assets' => array(
                'logistics/less/base.less',
            ),
            'filters' => array(
                '?LessFilter' => array(
                    'name' => LessFilter::class,
                ),
            ),
            'options' => array(
                'output' => 'logistics_css.css',
            ),
        ),
        'fullcalendar_css' => array(
            'assets' => array(
                'logistics/fullcalendar/fullcalendar.css',
            ),
            'filters' => array(
                '?CssFilter' => array(
                    'name' => CssFilter::class,
                ),
            ),
            'options' => array(
                'output' => 'fullcalendar_css.css',
            ),
        ),
        'logistics_js' => array(
            'assets' => array(
                'logistics/js/logistics.js',
                'logistics/fullcalendar/fullcalendar.js',
            ),
            'filters' => array(
                '?JsFilter' => array(
                    'name' => JsFilter::class,
                ),
            ),
        ),
        'minicolor_css' => array(
            'assets' => array(
                'logistics/minicolor/jquery.miniColors.css',
            ),
            'filters' => array(
                '?CssFilter' => array(
                    'name' => CssFilter::class,
                ),
            ),
            'options' => array(
                'output' => 'minicolor_css.css',
            ),
        ),
        'minicolor_js' => array(
            'assets' => array(
                'logistics/minicolor/jquery.miniColors.min.js',
            ),
            'filters' => array(
                '?JsFilter' => array(
                    'name' => JsFilter::class,
                ),
            ),
        ),
    ),
);
