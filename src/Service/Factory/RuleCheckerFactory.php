<?php
/**
 * Rule Handler Factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 23.03.2016 15:38
 */
namespace Popov\ZfcStatus\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Magere\Entity\Service\EntityService as ModuleService;
use Popov\ZfcStatus\Service\StatusService;
use Magere\Permission\Service\PermissionService;

use Magere\Entity\Model\Entity as Module;
use Popov\ZfcStatus\Service\RuleChecker;
use Popov\Current\Plugin\Current;

class RuleCheckerFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $sm) {
		$pm = $sm->get('ControllerPluginManager');
		$om = $sm->get('Doctrine\ORM\EntityManager');
		/** @var StatusService $statusService */
		//$statusService = $sm->get('StatusService');
		/** @var RuleHandler $ruleHandler */
		//$ruleHandler = $sm->get('RuleHandler');
		/** @var ModuleService $moduleService */
		//$moduleService = $sm->get('EntityService');
		/** @var PermissionService $permissionService */
		//$permissionService = $sm->get('PermissionService');

		/** @var Current $current */
		//$current = $pm->get('current');
		$user = $pm->get('user')->current();
		/** @var Module $module */
		//$module = $moduleService->getOneItem($current('module'), 'namespace');
		// Nothing change. Current module relative path not allowed
		//$module = $moduleService->getOneItem('Popov\ZfcStatus', 'namespace');

		//\Zend\Debug\Debug::dump($defaultStatus->getId()); die(__METHOD__);

		$ruler = new RuleChecker($user);

		//\Zend\Debug\Debug::dump($route->getParam('__NAMESPACE__')); die(__METHOD__);

		//if (!$pm->get('user')->isAdmin()) {
		//	$tree = $permissionService->getHumanReadablePermissionsTree($module, $user);
		//	$changer->setPermissionTree($tree[$module->getId()]);
		//}

		if ($ruler instanceof ObjectManagerAwareInterface) {
			$ruler->setObjectManager($om);
		}

		return $ruler;
	}

}