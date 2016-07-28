<?php
/**
 * Status helper factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 04.02.15 10:30
 */

namespace Agere\Status\View\Helper\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Agere\Status\View\Helper\Status as StatusHelper;

class StatusFactory {

	public function __invoke(ServiceLocatorInterface $vhm) {
		$sm = $vhm->getServiceLocator();
		$vhm = $sm->get('ViewHelperManager');

		$translator = $vhm->get('translate');
		$progressService = $sm->get('StatusProgressService');

		return new StatusHelper($sm->get('StatusService'), $progressService, $translator);
	}

}