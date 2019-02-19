<?php
/**
 * General Rule Handler
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 23.03.2016 15:55
 */
namespace Stagem\ZfcStatus\Service;

use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Stdlib\Exception;
use Popov\ZfcUser\Model\User;

class RuleChecker implements ObjectManagerAwareInterface {

	use ProvidesObjectManager;

	/** @var User */
	protected $__user;

	/** Item against which check conditions */
	protected $__item;

	public function __construct(User $user) {
		$this->__user = $user;
	}

	public function setUser(User $user) {
		$this->__user = $user;

		return $this;
	}

	public function getUser() {
		return $this->__user;
	}
	
	public function setItem($item) {
		$this->__item = $item;

		return $this;
	}
	
	public function getItem() {
		return $this->__item;
	}

	/**
	 * @todo Реалізувати декілька умов: item.isTrue == true && item.type == 'new'
	 * @param $rule
	 */
	public function check($rule) {
		$success = true;

		if ($rule->getConditions()) {
			$item = $this->getItem();
			$user = $this->getUser();
			
			$if = '
			$success = false;
			if (' . $rule->getConditions() . ') {
				$success = true;
			}			
			';
			
			eval($if);

            //\Zend\Debug\Debug::dump([$success]); die(__METHOD__ . __LINE__);

            //if ((!$handbookItem = $item->getHandbookItem()) || ($status = $handbookItem->getStatus()) && ($status->getMnemo() == 'confirmed')) {
			//	$success = true;
			//}	
			
			//\Zend\Debug\Debug::dump([($status = $handbookItem->getStatus()) == true, $status->getMnemo(), ($status->getMnemo() == 'confirmed'), $success, __METHOD__ . __LINE__]); //die(__METHOD__ . __LINE__);
		}		
		
		// @todo: Зробити псевдопарсер, щоб уникнути зламу система. Eval is not safe function!
		// Приклад: item.handbookItem.status == true && item.handbookItem.status.mnemo == 'confirmed'
		#$conditions = explode(';', $rule->getConditions());
		#foreach ($conditions as $condition) {
			#preg_match('/([a-zA-Z\.]+)[\s]{0,}([><!=]{0,})[\s]{0,}([a-zA-Z\.\']+)/', trim($condition), $matches);
			#$left = $this->getCheckedValue($matches[1]);
			#$right = $this->getCheckedValue($matches[3]);
			#$if = trim($matches[2]);

			#if ('==' === $if) {
			#	$success = ($left == $right);
			#} elseif ('===' === $if) {
			#	$success = ($left === $right);
			#} elseif ('!=' === $if) {
			#	$success = ($left != $right);
			#} elseif ('!==' === $if) {
			#	$success = ($left !== $right);
			#} elseif ('>' === $if) {
			#	$success = ($left > $right);
			#} elseif ('<' === $if) {
			#	$success = ($left < $right);
			#} elseif ('<>' === $if) {
			#	$success = ($left <> $right);
			#} elseif ('<=' === $if) {
			#	$success = ($left <= $right);
			#} elseif ('>=' === $if) {
			#	$success = ($left >= $right);
			#}
		#}
		
		return $success;
	}

	protected function getCheckedValue($quantifier) {
		$parts = explode('.', $quantifier);

		//$object = null;
		$value = null;
		foreach ($parts as $part) {
			// if scalar string value as 'statusName'
			if (in_array($part[0], ['"', "'"])) {
				//\Zend\Debug\Debug::dump([$part, __METHOD__ . __LINE__]);
				$value = trim($part, $part[0]);
				break;
			} elseif (in_array($part, ['true', 'false'])) {
				$value = ($part == 'true') ? true : false;
				break;
			}
			//\Zend\Debug\Debug::dump([$quantifier, get_class($this->__item), $part, gettype($value)]);
			if (!$value) {
				$property = '__' . $part;
				if (!isset($this->{$property})) {
					throw new Exception\RuntimeException(
						sprintf(
							'First element in quantifier %s must have relative property in class %s',
							$quantifier,
							__CLASS__
						)
					);
				}
				$value = $this->{$property};
				continue;
			}
			$method = 'get' . ucfirst($part);
			if (!method_exists($value, $method)) {
				$method = $part;
			}
			$value = $value->{$method}();
		}

		return $value;

		//\Zend\Debug\Debug::dump($value); die(__METHOD__ . __LINE__);
	}

}