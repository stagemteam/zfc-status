<?php
/**
 * Status workflow role
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 17.03.2016 19:31
 */
namespace Agere\Status\Model;

use Doctrine\ORM\Mapping as ORM;
use Popov\ZfcCore\Model\DomainAwareTrait;

class Rule {

	use DomainAwareTrait;

	protected $id;

	protected $name;

	protected $conditions;

	protected $priority;

	protected $status;

	//protected $allowedStatuses;

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return Rule
	 */
	public function setId($id) {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return Rule
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getConditions() {
		return $this->conditions;
	}

	/**
	 * @param mixed $conditions
	 * @return Rule
	 */
	public function setConditions($conditions) {
		$this->conditions = $conditions;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @param mixed $priority
	 * @return Rule
	 */
	public function setPriority($priority) {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param mixed $status
	 * @return Rule
	 */
	public function setStatus($status) {
		$this->status = $status;

		return $this;
	}

}