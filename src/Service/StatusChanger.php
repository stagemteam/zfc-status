<?php
/**
 * Global Status Changer
 *
 * @category Agere
 * @package Agere_Invoice
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 25.02.2016 23:31
 */
namespace Agere\Status\Service;

use Zend\Stdlib\Exception;

use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

use Agere\Permission\Model\PermissionAccess;
//use Magere\Status\Service\StatusService;
use Agere\Status\Service\RuleChecker;
use Agere\Status\Model\Status;
use Agere\Module\Model\Module;

class StatusChanger implements ObjectManagerAwareInterface {

	use ProvidesObjectManager;

	const DEFAULT_STATUS_MNEMO = 'draft';

	/** @var StatusService */
	protected $statusService;

	/** @var RuleChecker */
	protected $ruleHandler;

	/** @var array */
	protected $tree;

	protected $defaultStatus;

	/** @var Module */
	protected $module;

	protected $item;

	/**
	 * Status can be saved in related object with one-to-one relation.
	 * Object which contain status is assigned in this property
	 */
	protected $itemWithStatus;

	/** @var Status */
	protected $oldStatus;

	/** @var Status */
	protected $newStatus;


	static protected $instance;

	public function __construct(StatusService $statusService) {
		$this->statusService = $statusService;

		//self::$instance = $this;
	}


	/*static public function getInstance() {
		return self::$instance;
	}*/

	public function getStatusService() {
		return $this->statusService;
	}

	public function setRuleChecker(RuleChecker $ruleHandler) {
		$this->ruleHandler = $ruleHandler;

		return $this;
	}

	public function getRuleChecker() {
		return $this->ruleHandler;
	}

	public function setPermissionTree(array $tree) {
		$this->tree = $tree;

		return $this;
	}

	public function getPermissionTree() {
		return $this->tree;
	}

	public function setDefaultStatus($defaultStatus) {
		$this->defaultStatus = $defaultStatus;

		return $this;
	}

	public function getDefaultStatus() {
		/*if (!$this->defaultStatus) {
			$this->defaultStatus = $this->getStatusService()->getOneItemByMnemo(
				StatusChanger::DEFAULT_STATUS_MNEMO,
				$this->getModule()->getMnemo()
			);
		}*/
		if (!$this->defaultStatus) {
			$this->defaultStatus = $this->getStatusService()->getRepository()
				->findOneBy(['module' => $this->getModule(), 'automatically' => 1]);
		}
		return $this->defaultStatus;
	}

	/**
	 * Module for object which will be change status
	 *
	 * @param Module $module
	 * @return $this
	 */
	public function setModule(Module $module) {
		$this->module = $module;

		return $this;
	}

	public function getModule() {
		return $this->module;
	}

	public function setItem($item) {
		$this->item = $item;
		$this->assignItemWithStatus($item);

		$itemWithStatus = $this->getItemWithStatus();
		$this->oldStatus = ($oldStatus = $itemWithStatus->getStatus())
			? $oldStatus
			: $this->getDefaultStatus();

		//\Zend\Debug\Debug::dump(get_class($this->oldStatus)); die(__METHOD__);

		return $this;
	}

	public function getItem() {
		return $this->item;
	}

	public function getItemWithStatus() {
		return $this->itemWithStatus;
	}

	public function getOldStatus() {
		return $this->oldStatus;
	}

	public function getNewStatus() {
		return $this->newStatus;
	}

	/**
	 * Does item contain status object? If not then status is saved in related object.
	 *
	 * @param $item
	 * @return bool
	 * @link https://github.com/borisguery/bgylibrary/blob/master/library/Bgy/Doctrine/EntitySerializer.php#L87
	 */
	public function assignItemWithStatus($item) {
		static $depth = 0, $maxDepth = 1;

		if (method_exists($item, 'getStatus')) {
			$this->itemWithStatus = $item;

			return true;
		} elseif ($depth < $maxDepth) {
			/** @var ClassMetadata $metadata */
			$om = $this->getObjectManager();
			$className = get_class($item);
			$metadata = $om->getClassMetadata($className);

			foreach ($metadata->associationMappings as $field => $mapping) {
				if ($mapping['type'] === ClassMetadata::ONE_TO_ONE) {
					$getter = 'get' . ucfirst($field);
					$targetEntity = $mapping['targetEntity'];
					$itemWithStatus = $item->{$getter}() ?: new $targetEntity();

					$depth++;
					$isAssign = $this->assignItemWithStatus($itemWithStatus);
					$depth--;

					if ($isAssign) {
						$setter = 'set' . ucfirst($field);
						$itemWithStatus->getId() ? : $item->{$setter}($itemWithStatus);

						return $isAssign;
					}
				}
			}

			throw new Exception\RuntimeException(sprintf(
				'Cannot find Status object in "%s" and related objects with relation One-To-One.',
				$className
			));
		}

		return false;
	}



	/**
	 * @param object $status Status mnemo
	 * @return bool
	 */
	//public function changeTo($mnemo) {
	public function changeTo($status) {
		if (!$this->canChangeTo($status)) {
			return false;
		}

		$itemWithStatus = $this->getItemWithStatus();
		$itemWithStatus->setStatus($status);
		$this->newStatus = $status;

		return true;
	}

	/**
	 * Can change status from current to passed
	 *
	 * @param int|Status $status Status object or identifier of Status
	 * @return bool
	 */
	public function canChangeTo($status) {
		//$isAdmin = !isset($this->tree); // if $tree is null that mean current user is admin
		if ($this->isAdmin()) {
			return true;
		}

		if (is_int($status)) {
			$status = $this->getStatusService()->getOneItem($status);
		}

		// Налаштувати правило як написала Іра в коментарях
		$oldStatus = $this->getOldStatus();
		//$settings = $this->tree['settings'];


		//\Zend\Debug\Debug::dump([
		//	$settings,
		//	__METHOD__.__LINE__
		//]);


		//\Zend\Debug\Debug::dump([$oldStatus->getId(), $this->tree['settings']]); die(__METHOD__);
		/*if (isset($settings['changeWith'][$oldStatus->getId()])
			&& isset($settings['change'][$status->getId()])
			&& ($settings['changeWith'][$oldStatus->getId()] === PermissionAccess::PERMISSION_WRITE)
			&& ($settings['change'][$status->getId()] === PermissionAccess::PERMISSION_WRITE)
			&& $this->checkRule($status->getRule()) // чи має права переводити в новий статус
			//&& $this->checkRule($oldStatus->getRule())
		) {
			//\Zend\Debug\Debug::dump(__METHOD__ . __LINE__);
			return true;
		}*/
		if ($this->isAllowChangeFrom($oldStatus) && $this->isAllowChangeTo($status)) {
			//\Zend\Debug\Debug::dump([$status->getMnemo(), __METHOD__ . __LINE__]);
			return true;
		}

		return false;
	}

	/**
	 * If $tree is null that mean current user is admin
	 *
	 * @return bool
	 */
	public function isAdmin() {
		$isAdmin = !isset($this->tree);

		return $isAdmin;
	}

	/**
	 * Is access to change from old status
	 *
	 * @param $status
	 * @return bool
	 */
	public function isAllowChangeFrom($status) {
		if ($this->isAdmin()) {
			return true;
		}

		$settings = $this->tree['settings'];

		if (isset($settings['changeWith'][$status->getId()])
			&& ($settings['changeWith'][$status->getId()] === PermissionAccess::PERMISSION_WRITE)
		) {
			//\Zend\Debug\Debug::dump(__METHOD__ . __LINE__);
			return true;
		}
		return false;
	}

	/**
	 * Is access to change to new status
	 *
	 * @param $status
	 * @return bool
	 */
	public function isAllowChangeTo($status) {
		if ($this->isAdmin()) {
			return true;
		}

		$settings = $this->tree['settings'];

		if (isset($settings['change'][$status->getId()])
			&& ($settings['change'][$status->getId()] === PermissionAccess::PERMISSION_WRITE)
			&& $this->checkRule($status->getRule()) // чи має права переводити в новий статус
		) {
			//\Zend\Debug\Debug::dump(__METHOD__ . __LINE__);
			return true;
		}

		return false;
	}

	public function checkRule($rule) {
		if ($rule === null) {
			return true;
		}

		//\Zend\Debug\Debug::dump([$this->getRuleChecker()->setItem($this->getItem())->check($rule), __METHOD__.__LINE__]); //die();

		return $this->getRuleChecker()->setItem($this->getItem())->check($rule);
	}

}