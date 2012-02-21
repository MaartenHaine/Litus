<?php
/**
 * Litus is a project by a group of students from the K.U.Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Michiel Staessen <michiel.staessen@litus.cc>
 * @author Alan Szepieniec <alan.szepieniec@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */
 
return array(
	'controllers'  => array(
		'admin_article' => array(
			'@common_jquery',
		    '@admin_css',
		    '@admin_js',
		    '@bootstrap_js_modal',
		),
		'admin_booking' => array(
			'@common_jquery',
		    '@admin_css',
		    '@admin_js',
		),
		'admin_stock' => array(
			'@common_jquery',
		    '@admin_css',
		    '@admin_js',
		),
		'admin_order' => array(
			'@common_jquery',
		    '@admin_css',
		    '@admin_js',
		),
		'admin_delivery' => array(
			'@common_jquery',
		    '@admin_css',
		    '@admin_js',
		),
		'admin_sale' => array(
			'@common_jquery',
		    '@admin_css',
		    '@admin_js',
		),
	),
	'routes' => array(),
);