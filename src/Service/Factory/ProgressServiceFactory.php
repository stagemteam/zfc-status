<?php
/**
 * Progress Status Service Factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 04.02.15 10:30
 */

namespace Stagem\ZfcStatus\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\PluginManager;
use Stagem\ZfcStatus\Service\ProgressService;
use Magere\Entity\Controller\Plugin\ModulePlugin;

class ProgressServiceFactory {

    public function __invoke(ContainerInterface $container)
    {
        /** @var PluginManager $cpm */
		$cpm = $container->get('ControllerPluginManager');
        $userService = $container->get('UserService');
		//$user = $cpm->get('user');
        /** @var ModulePlugin $modulePlugin */
		$modulePlugin = $cpm->get('module');

		return (new ProgressService($userService->getCurrent(), $modulePlugin));
	}

}