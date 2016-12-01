<?php
/**
 * Status Changer Factory
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 26.02.2016 0:21
 */
namespace Agere\Status\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Agere\Module\Service\ModuleService;
use Agere\Status\Service\StatusService;
use Magere\Permission\Service\PermissionService;

use Agere\Entity\Model\Entity as Module;
use Agere\Status\Service\StatusChanger;
use Magere\Status\Service\RuleChecker;
use Agere\Current\Plugin\Current;

class StatusChangerFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $sm) {
		$cpm = $sm->get('ControllerPluginManager');
		$om = $sm->get('Doctrine\ORM\EntityManager');
		/** @var StatusService $statusService */
		$statusService = $sm->get('StatusService');
		/** @var ModuleService $moduleService */
		$moduleService = $sm->get('ModuleService');
		/** @var PermissionService $permissionService */
		$permissionService = $sm->get('PermissionService');
		$ruleChecker = $sm->get('RuleChecker');

		/** @var Current $current */
		$current = $cpm->get('current');
		$modulePlugin = $cpm->get('module');
		$user = $cpm->get('user')->current();
		/** @var Module $module */
		//$module = $moduleService->getOneItem($current('module'), 'namespace');
		// Nothing change. Current module relative path not allowed
		$module = $moduleService->getRepository()->findOneBy(['namespace' => 'Agere\Status']);

		$changer = new StatusChanger($statusService);
		//$changer->setModule($module);
		$changer->setRuleChecker($ruleChecker);

		//\Zend\Debug\Debug::dump($pm->get('user')->isAdmin()); die(__METHOD__);

		/*if (!$cpm->get('user')->isAdmin()) {
			$tree = $permissionService->getHumanReadablePermissionTree($module, $user);
			$changer->setPermissionTree($tree[$module->getId()]);
		}*/

		if ($changer instanceof ObjectManagerAwareInterface) {
			$changer->setObjectManager($om);
		}

		return $changer;
	}

}