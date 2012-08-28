<?php

namespace GalleryBundle\Controller\Admin;

/**
 * InstallController
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class InstallController extends \CommonBundle\Component\Controller\ActionController\InstallController
{
	protected function _initConfig()
	{
		$this->_installConfig(
	        array(
				array(
					'key'         => 'gallery.path',
					'value'       => '/_gallery/albums',
					'description' => 'The path to the gallery albums',
				)
			)
		);
	}

	protected function _initAcl()
	{
	    $this->installAclStructure(
	        array(
	            'galleryBundle' => array(
	                'admin_gallery' => array(
	                    'add', 'addPhotos', 'censorPhoto', 'delete', 'edit', 'manage', 'photos', 'upload'
	                ),
	                'common_gallery' => array(
	                    'album', 'overview', 'year'
	                )
	            )
	        )
	    );
	}
}
