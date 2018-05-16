<?php
/**
 * Status Changer Factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 26.02.2016 0:21
 */
namespace Popov\ZfcStatus\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Magere\Entity\Service\EntityService as ModuleService;
use Popov\ZfcStatus\Service\StatusService;
use Magere\Permission\Service\PermissionService;

use Magere\Entity\Model\Entity as Module;
use Popov\ZfcStatus\Service\StatusChanger;
use Popov\ZfcStatus\Service\RuleChecker;
use Popov\Current\Plugin\Current;

class StatusChangerFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $sm) {
		$cpm = $sm->get('ControllerPluginManager');
		$om = $sm->get('Doctrine\ORM\EntityManager');
		/** @var StatusService $statusService */
		$statusService = $sm->get('StatusService');
		/** @var ModuleService $moduleService */
		$moduleService = $sm->get('EntityService');
		$modulePlugin = $cpm->get('module');
		/** @var PermissionService $permissionService */
		$permissionService = $sm->get('PermissionService');
		$ruleChecker = $sm->get('RuleChecker');

		/** @var Current $current */
		$current = $cpm->get('current');
		$user = $cpm->get('user')->current();
		/** @var Module $module */
		//$module = $moduleService->getOneItem($current('module'), 'namespace');
		// Nothing change. Current module relative path not allowed
		$module = $moduleService->getOneItem('Popov\ZfcStatus', 'namespace');

		//\Zend\Debug\Debug::dump($defaultStatus->getId()); die(__METHOD__);

		$changer = new StatusChanger($statusService, $modulePlugin);
		//$changer->setModule($module);
		$changer->setRuleChecker($ruleChecker);

		//\Zend\Debug\Debug::dump($pm->get('user')->isAdmin()); die(__METHOD__);

		if (!$cpm->get('user')->isAdmin()) {
			$tree = $permissionService->getHumanReadablePermissionTree($module, $user);
            // has status settings save in database
            if (isset($tree[$module->getId()])) {
                $changer->setPermissionTree($tree[$module->getId()]);
            }
		}

		if ($changer instanceof ObjectManagerAwareInterface) {
			$changer->setObjectManager($om);
		}

		return $changer;
	}

}