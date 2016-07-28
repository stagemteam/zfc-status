<?php
/**
 * Statusable plugin factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 04.02.15 10:30
 */
namespace Agere\Status\Controller\Plugin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\View\HelperPluginManager;
use Agere\Status\Controller\Plugin\Statusable;

class StatusableFactory
{
    public function __invoke(ServiceLocatorInterface $cpm)
    {
        $sm = $cpm->getServiceLocator();
        //$om = $sm->get('Doctrine\ORM\EntityManager');
        //$cm = $sm->get('ControllerPluginManager');
        //$vm = $sm->get('ViewHelperManager');
        $config = $sm->get('Config');
        $url = $cpm->get('url');
        $module = $cpm->get('module');
        //$route = $current('route');
        $changer = $sm->get('StatusChanger');
        $moduleService = $sm->get('EntityService');

        return (new Statusable($changer))
            ->injectUrl($url)
            ->injectConfig($config)
            ->injectModule($module);
    }
}