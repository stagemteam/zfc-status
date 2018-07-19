<?php

namespace Popov\ZfcStatus;

use Zend\EventManager\EventInterface;
use Popov\ZfcStatus\Listener;

class Module
{
    /*public function onBootstrap(EventInterface $e)
    {
        $eventManager = $e->getTarget()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $eventManager->attach((new Listener\StatusListener())->setServiceLocator($serviceManager));
    }*/

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
