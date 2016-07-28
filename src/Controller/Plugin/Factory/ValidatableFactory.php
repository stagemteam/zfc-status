<?php
/**
 * Validatable plugin factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 11.05.16 2:38
 */
namespace Agere\Status\Controller\Plugin\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Agere\Status\Controller\Plugin\Validatable;

class ValidatableFactory
{
    public function __invoke(ServiceLocatorInterface $cpm)
    {
        $sm = $cpm->getServiceLocator();
        $config = $sm->get('Config');

        return (new Validatable())
            ->injectConfig($config);
    }
}