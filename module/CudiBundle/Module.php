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

namespace CudiBundle;

use Zend\EventManager\Event,
	Zend\EventManager\StaticEventManager,
	Zend\Module\Consumer\AutoloaderProvider,
	Zend\Module\Manager,
    Zend\Mvc\MvcEvent,
    Zend\View\Helper\Doctype;

class Module implements AutoloaderProvider
{
	protected $locator = null;
	protected $moduleManager = null;

	public function init(Manager $moduleManager)
    {
    	$this->moduleManager = $moduleManager;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ 	=> __DIR__ . '/src/' . __NAMESPACE__,
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/src/Resources/config/module.config.php';
    }

    public function initializeView(Event $e)
    {
        /*$app = $e->getParam('application');
        $locator = $app->getLocator();
        $view = $locator->get('view');
		
		$view->getEnvironment()->getLoader()->addPath(__DIR__ . '/../CudiBundle/src/Resources/layouts');
        $view->getEnvironment()->getLoader()->addPath(__DIR__ . '/src/Resources/views');

        $url = $view->plugin('url');
        $url->setRouter($app->getRouter());
        
        $view->plugin('doctype')->setDoctype(Doctype::HTML5);
        $view->plugin('headTitle')->setSeparator('&mdash;');*/
    }
    
    public function getProvides()
    {
        return array(
            'name'    => 'CudiBundle',
            'version' => '1.0.0',
        );
    }
}