<?php
/**
 * Status plugin factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 11.05.16 2:38
 */
namespace Stagem\ZfcStatus\Controller\Plugin\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Stagem\ZfcStatus\Controller\Plugin\StatusPlugin;
use Stagem\ZfcStatus\Service\Progress\StatusContext;

class StatusPluginFactory
{
    public function __invoke(ServiceLocatorInterface $cpm)
    {
        $sm = $cpm->getServiceLocator();
        $fem = $sm->get('FormElementManager');
        $statusService = $sm->get('StatusService');
        $progressService = $sm->get('ProgressService');
        $statusChanger = $sm->get('StatusChanger');
        $contextClosure = function() use ($sm) {
            return $sm->get(StatusContext::class);
        };

        return (new StatusPlugin($statusService, $progressService, $statusChanger, $contextClosure))
            ->setFormElementManager($fem);
    }
}