<?php
/**
 * Status Changer Factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 26.02.2016 0:21
 */
namespace Stagem\ZfcStatus\Service\Factory;

use Popov\ZfcEntity\Helper\ModuleHelper;
use Popov\ZfcEntity\Model\Entity;
use Popov\ZfcForm\FormElementManager;
use Popov\ZfcUser\Helper\UserHelper;
use Psr\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Popov\ZfcEntity\Service\ModuleService;
use Stagem\ZfcStatus\Service\StatusService;
use Popov\ZfcPermission\Service\PermissionService;

use Popov\ZfcEntity\Model\Module;
use Stagem\ZfcStatus\Service\StatusChanger;
use Stagem\ZfcStatus\Service\RuleChecker;
use Popov\ZfcCurrent\CurrentHelper;

class StatusChangerFactory {

	public function __invoke(ContainerInterface $container) {
		$om = $container->get('Doctrine\ORM\EntityManager');

		$elementManager = $container->get(FormElementManager::class);

		/** @var StatusService $statusService */
		$statusService = $container->get('StatusService');


		/** @var ModuleHelper $moduleHelper */
		$moduleHelper = $container->get(ModuleHelper::class);

		/** @var PermissionService $permissionService */
		$permissionService = $container->get('PermissionService');
		$ruleChecker = $container->get('RuleChecker');

		/** @var CurrentHelper $current */
		//$current = $container->get(CurrentHelper::class);
        $userHelper = $container->get(UserHelper::class);
        $user = $userHelper->current();
		/** @var Module $entity */
		//$module = $moduleService->getOneItem($current('module'), 'namespace');
		// Nothing change. Current module relative path not allowed
		$entity = $om->getRepository(Entity::class)->findOneBy(['namespace' => Entity::class]);

		//\Zend\Debug\Debug::dump($defaultStatus->getId()); die(__METHOD__);

		$changer = new StatusChanger($statusService, $moduleHelper, $elementManager);

		//$changer->setModule($module);
		$changer->setRuleChecker($ruleChecker);


		if (!$userHelper->isAdmin()) {
			$tree = $permissionService->getHumanReadablePermissionTree($entity, $user);
            // has status settings save in database
            if (isset($tree[$entity->getId()])) {
                $changer->setPermissionTree($tree[$entity->getId()]);
            }
		}

		if ($changer instanceof ObjectManagerAwareInterface) {
			$changer->setObjectManager($om);
		}

		return $changer;
	}

}