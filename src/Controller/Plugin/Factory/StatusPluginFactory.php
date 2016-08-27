<?php
/**
 * Status plugin factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 11.05.16 2:38
 */
namespace Agere\Status\Controller\Plugin\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Agere\Status\Controller\Plugin\StatusPlugin;

class StatusPluginFactory
{
    public function __invoke(ServiceLocatorInterface $cpm)
    {
        $sm = $cpm->getServiceLocator();
        $fem = $sm->get('FormElementManager');

        return (new StatusPlugin())
            ->setFormElementManager($fem);
    }
}