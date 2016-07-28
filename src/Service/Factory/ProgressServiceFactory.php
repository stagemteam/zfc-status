<?php
/**
 * Progress Status Service Factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 04.02.15 10:30
 */

namespace Agere\Status\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Agere\Status\Service\ProgressService;

class ProgressServiceFactory {

	public function __invoke(ServiceLocatorInterface $sm) {
		//$vhm = $sm->get('ViewHelperManager');
		$cpm = $sm->get('ControllerPluginManager');
		$user = $cpm->get('user');
		$module = $cpm->get('module');

		return (new ProgressService($user->current(), $module));
	}

}