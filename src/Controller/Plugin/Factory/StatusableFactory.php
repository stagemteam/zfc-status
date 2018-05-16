<?php
/**
 * Statusable plugin factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 04.02.15 10:30
 */
namespace Popov\ZfcStatus\Controller\Plugin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\View\HelperPluginManager;
use Popov\ZfcStatus\Controller\Plugin\Statusable;

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
        //$moduleService = $sm->get('EntityService');

        return (new Statusable($changer))->setTranslator($sm->get('translator'))
            ->injectUrl($url)
            ->injectConfig($config)
            ->injectModulePlugin($module);
    }
}