<?php
/**
 * Rule Handler Factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 23.03.2016 15:38
 */
namespace Agere\Status\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Agere\Entity\Service\EntityService as ModuleService;
use Agere\Status\Service\StatusService;
use Magere\Permission\Service\PermissionService;

use Agere\Entity\Model\Entity as Module;
use Agere\Status\Service\RuleChecker;
use Agere\Current\Plugin\Current;

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
		//$module = $moduleService->getOneItem('Magere\Status', 'namespace');

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