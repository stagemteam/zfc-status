<?php
namespace Agere\Status\Service\Factory;

use Agere\Status\Service\AbstractEntityService;

class Helper extends AbstractEntityService {

	/**
	 * @param string $key
	 * @param $em
	 */
	public static function create($key, $em) {
        //\Zend\Debug\Debug::dump(debug_backtrace()[1]['function']);
		$explode = explode('/', $key);
		$key = $explode[1];
		$explode[0] = ucfirst($explode[0]);
		$explode[1] = ucfirst($explode[1]);
		if (!isset(self::$_services[$key])) {
			$classService = "\\Agere\\$explode[0]\\Service\\$explode[1]Service";
			self::$_services[$key] = new $classService;
		}
		if (!isset(self::$_repositories[$key])) {
			$cmd = new \Doctrine\ORM\Mapping\ClassMetadata("Agere\\$explode[0]\\Model\\$explode[1]");
			$classRepo = "\\Agere\\$explode[0]\\Model\\Repository\\$explode[1]Repository";
			self::$_repositories[$key] = new $classRepo($em, $cmd);
		}

		return self::$_services[$key];
	}

}