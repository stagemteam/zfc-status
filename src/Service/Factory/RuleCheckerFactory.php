<?php
/**
 * Rule Handler Factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 23.03.2016 15:38
 */
namespace Stagem\ZfcStatus\Service\Factory;

use Psr\Container\ContainerInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Popov\ZfcUser\Helper\UserHelper;
use Stagem\ZfcStatus\Service\RuleChecker;

class RuleCheckerFactory {

	public function __invoke(ContainerInterface $container) {
		$om = $container->get('Doctrine\ORM\EntityManager');
		$user = $container->get(UserHelper::class)->current();

		$ruler = new RuleChecker($user);

		if ($ruler instanceof ObjectManagerAwareInterface) {
			$ruler->setObjectManager($om);
		}

		return $ruler;
	}

}