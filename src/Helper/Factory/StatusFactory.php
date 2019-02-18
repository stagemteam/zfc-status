<?php
/**
 * Status helper factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 04.02.15 10:30
 */

namespace Stagem\ZfcStatus\View\Helper\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Stagem\ZfcStatus\View\Helper\StatusHelper as StatusHelper;

class StatusFactory {

	public function __invoke(ServiceLocatorInterface $vhm) {
		$sm = $vhm->getServiceLocator();
		$vhm = $sm->get('ViewHelperManager');

		$translator = $vhm->get('Translate');
        $statusService = $sm->get('StatusService');
		$progressService = $sm->get('ProgressService');
		$statusChanger = $sm->get('StatusChanger');

		return new StatusHelper($statusService, $progressService, $statusChanger, $translator);
	}

}