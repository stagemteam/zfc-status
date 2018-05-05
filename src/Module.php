<?php
namespace Agere\Status;

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;
use Zend\EventManager\EventInterface;


use Agere\Status\Listener;

class Module
{
    public function onBootstrap(EventInterface $e) {
        $eventManager = $e->getTarget()->getEventManager();
        $sm = $e->getApplication()->getServiceManager();

        //$eventManager->attach((new Listener\StatusListener())->setServiceLocator($sm));

    }


    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

	/*public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'status' => function($sm) {
					$locator = $sm->getServiceLocator();
					return new \Magere\Status\View\Helper\Status($locator->get('StatusService'), $locator->get('ViewHelperManager')->get('translate'));
				},
			),
		);
	}*/

}
