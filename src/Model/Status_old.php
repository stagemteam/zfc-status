<?php
namespace Agere\Status\Model;

use Doctrine\ORM\Mapping as ORM;
use Agere\Core\Model\DomainAwareTrait;

/**
 * Status
 */
class Status2 {

	use DomainAwareTrait;

	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $mnemo;

	/**
	 * @var integer
	 */
	private $entityId;

	/**
	 * @var string
	 */
	private $hidden;

	/**
	 * @var string
	 */
	private $remove;

	/**
	 * @var string
	 */
	private $automatically;

	/**
	 * @var string
	 */
	private $color;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $mail;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $warranty;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $workWarranty;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $logistics;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $logisticsWith;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $orderSale;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $clients;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $shopSpares;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $shopSparesItem;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $shopPromotionalProducts;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $shopPromotionalProductsItem;

	/**
	 * @var \Magere\Entity\Model\Entity
	 */
	private $entity;

	/** @var Status[]  */
	private $workflow;

	/** @var Rule */
	private $rule;

	/** @var Progress */
	private $progress;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->mail = new \Doctrine\Common\Collections\ArrayCollection();
		$this->warranty = new \Doctrine\Common\Collections\ArrayCollection();
		$this->workWarranty = new \Doctrine\Common\Collections\ArrayCollection();
		$this->logistics = new \Doctrine\Common\Collections\ArrayCollection();
		$this->logisticsWith = new \Doctrine\Common\Collections\ArrayCollection();
		$this->orderSale = new \Doctrine\Common\Collections\ArrayCollection();
		$this->clients = new \Doctrine\Common\Collections\ArrayCollection();
		$this->shopSpares = new \Doctrine\Common\Collections\ArrayCollection();
		$this->shopSparesItem = new \Doctrine\Common\Collections\ArrayCollection();
		$this->shopPromotionalProducts = new \Doctrine\Common\Collections\ArrayCollection();
		$this->shopPromotionalProductsItem = new \Doctrine\Common\Collections\ArrayCollection();

		$this->workflow = new \Doctrine\Common\Collections\ArrayCollection();
		$this->progress = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 * @return Status
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set mnemo
	 *
	 * @param string $mnemo
	 * @return Status
	 */
	public function setMnemo($mnemo) {
		$this->mnemo = $mnemo;

		return $this;
	}

	/**
	 * Get mnemo
	 *
	 * @return string
	 */
	public function getMnemo() {
		return $this->mnemo;
	}

	/**
	 * Set entityId
	 *
	 * @param integer $entityId
	 * @return Status
	 */
	public function setEntityId($entityId) {
		$this->entityId = $entityId;

		return $this;
	}

	/**
	 * Get entityId
	 *
	 * @return integer
	 */
	public function getEntityId() {
		return $this->entityId;
	}

	/**
	 * Set hidden
	 *
	 * @param string $hidden
	 * @return Status
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;

		return $this;
	}

	/**
	 * Get hidden
	 *
	 * @return string
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * Set remove
	 *
	 * @param string $remove
	 * @return Status
	 */
	public function setRemove($remove) {
		$this->remove = $remove;

		return $this;
	}

	/**
	 * Get remove
	 *
	 * @return string
	 */
	public function getRemove() {
		return $this->remove;
	}

	/**
	 * Set automatically
	 *
	 * @param string $automatically
	 * @return Status
	 */
	public function setAutomatically($automatically) {
		$this->automatically = $automatically;

		return $this;
	}

	/**
	 * Get automatically
	 *
	 * @return string
	 */
	public function getAutomatically() {
		return $this->automatically;
	}

	/**
	 * Set color
	 *
	 * @param string $color
	 * @return Status
	 */
	public function setColor($color) {
		$this->color = $color;

		return $this;
	}

	/**
	 * Get color
	 *
	 * @return string
	 */
	public function getColor() {
		return $this->color;
	}

	/**
	 * Add mail
	 *
	 * @param \Magere\Mail\Model\Mail $mail
	 * @return Status
	 */
	public function addMail(\Magere\Mail\Model\Mail $mail) {
		$this->mail[] = $mail;

		return $this;
	}

	/**
	 * Remove mail
	 *
	 * @param \Magere\Mail\Model\Mail $mail
	 */
	public function removeMail(\Magere\Mail\Model\Mail $mail) {
		$this->mail->removeElement($mail);
	}

	/**
	 * Get mail
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getMail() {
		return $this->mail;
	}

	/**
	 * Add warranty
	 *
	 * @param \Magere\Warranty\Model\Warranty $warranty
	 * @return Status
	 */
	public function addWarranty(\Magere\Warranty\Model\Warranty $warranty) {
		$this->warranty[] = $warranty;

		return $this;
	}

	/**
	 * Remove warranty
	 *
	 * @param \Magere\Warranty\Model\Warranty $warranty
	 */
	public function removeWarranty(\Magere\Warranty\Model\Warranty $warranty) {
		$this->warranty->removeElement($warranty);
	}

	/**
	 * Get warranty
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getWarranty() {
		return $this->warranty;
	}

	/**
	 * Add workWarranty
	 *
	 * @param \Magere\Warranty\Model\Warranty $workWarranty
	 * @return Status
	 */
	public function addWorkWarranty(\Magere\Warranty\Model\Warranty $workWarranty) {
		$this->workWarranty[] = $workWarranty;

		return $this;
	}

	/**
	 * Remove workWarranty
	 *
	 * @param \Magere\Warranty\Model\Warranty $workWarranty
	 */
	public function removeWorkWarranty(\Magere\Warranty\Model\Warranty $workWarranty) {
		$this->workWarranty->removeElement($workWarranty);
	}

	/**
	 * Get workWarranty
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getWorkWarranty() {
		return $this->workWarranty;
	}

	/**
	 * Add logistics
	 *
	 * @param \Magere\Logistics\Model\Logistics $logistics
	 * @return Status
	 */
	public function addLogistic(\Magere\Logistics\Model\Logistics $logistics) {
		$this->logistics[] = $logistics;

		return $this;
	}

	/**
	 * Remove logistics
	 *
	 * @param \Magere\Logistics\Model\Logistics $logistics
	 */
	public function removeLogistic(\Magere\Logistics\Model\Logistics $logistics) {
		$this->logistics->removeElement($logistics);
	}

	/**
	 * Get logistics
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getLogistics() {
		return $this->logistics;
	}

	/**
	 * Add logisticsWith
	 *
	 * @param \Magere\Logistics\Model\Logistics $logisticsWith
	 * @return Status
	 */
	public function addLogisticsWith(\Magere\Logistics\Model\Logistics $logisticsWith) {
		$this->logisticsWith[] = $logisticsWith;

		return $this;
	}

	/**
	 * Remove logisticsWith
	 *
	 * @param \Magere\Logistics\Model\Logistics $logisticsWith
	 */
	public function removeLogisticsWith(\Magere\Logistics\Model\Logistics $logisticsWith) {
		$this->logisticsWith->removeElement($logisticsWith);
	}

	/**
	 * Get logisticsWith
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getLogisticsWith() {
		return $this->logisticsWith;
	}

	/**
	 * Add orderSale
	 *
	 * @param \Magere\OrderSale\Model\OrderSale $orderSale
	 * @return Status
	 */
	public function addOrderSale(\Magere\OrderSale\Model\OrderSale $orderSale) {
		$this->orderSale[] = $orderSale;

		return $this;
	}

	/**
	 * Remove orderSale
	 *
	 * @param \Magere\OrderSale\Model\OrderSale $orderSale
	 */
	public function removeOrderSale(\Magere\OrderSale\Model\OrderSale $orderSale) {
		$this->orderSale->removeElement($orderSale);
	}

	/**
	 * Get orderSale
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getOrderSale() {
		return $this->orderSale;
	}

	/**
	 * Add clients
	 *
	 * @param \Magere\Clients\Model\Clients $clients
	 * @return Status
	 */
	public function addClient(\Magere\Clients\Model\Clients $clients) {
		$this->clients[] = $clients;

		return $this;
	}

	/**
	 * Remove clients
	 *
	 * @param \Magere\Clients\Model\Clients $clients
	 */
	public function removeClient(\Magere\Clients\Model\Clients $clients) {
		$this->clients->removeElement($clients);
	}

	/**
	 * Get clients
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getClients() {
		return $this->clients;
	}

	/**
	 * Add shopSpares
	 *
	 * @param \Magere\Spares\Model\ShopSpares $shopSpares
	 * @return Status
	 */
	public function addShopSpare(\Magere\Spares\Model\ShopSpares $shopSpares) {
		$this->shopSpares[] = $shopSpares;

		return $this;
	}

	/**
	 * Remove shopSpares
	 *
	 * @param \Magere\Spares\Model\ShopSpares $shopSpares
	 */
	public function removeShopSpare(\Magere\Spares\Model\ShopSpares $shopSpares) {
		$this->shopSpares->removeElement($shopSpares);
	}

	/**
	 * Get shopSpares
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getShopSpares() {
		return $this->shopSpares;
	}

	/**
	 * Add shopSparesItem
	 *
	 * @param \Magere\Spares\Model\ShopSparesItem $shopSparesItem
	 * @return Status
	 */
	public function addShopSparesItem(\Magere\Spares\Model\ShopSparesItem $shopSparesItem) {
		$this->shopSparesItem[] = $shopSparesItem;

		return $this;
	}

	/**
	 * Remove shopSparesItem
	 *
	 * @param \Magere\Spares\Model\ShopSparesItem $shopSparesItem
	 */
	public function removeShopSparesItem(\Magere\Spares\Model\ShopSparesItem $shopSparesItem) {
		$this->shopSparesItem->removeElement($shopSparesItem);
	}

	/**
	 * Get shopSparesItem
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getShopSparesItem() {
		return $this->shopSparesItem;
	}

	/**
	 * Add shopPromotionalProducts
	 *
	 * @param \Magere\PromotionalProducts\Model\ShopPromotionalProducts $shopPromotionalProducts
	 * @return Status
	 */
	public function addShopPromotionalProduct(\Magere\PromotionalProducts\Model\ShopPromotionalProducts $shopPromotionalProducts) {
		$this->shopPromotionalProducts[] = $shopPromotionalProducts;

		return $this;
	}

	/**
	 * Remove shopPromotionalProducts
	 *
	 * @param \Magere\PromotionalProducts\Model\ShopPromotionalProducts $shopPromotionalProducts
	 */
	public function removeShopPromotionalProduct(\Magere\PromotionalProducts\Model\ShopPromotionalProducts $shopPromotionalProducts) {
		$this->shopPromotionalProducts->removeElement($shopPromotionalProducts);
	}

	/**
	 * Get shopPromotionalProducts
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getShopPromotionalProducts() {
		return $this->shopPromotionalProducts;
	}

	/**
	 * Add shopPromotionalProductsItem
	 *
	 * @param \Magere\PromotionalProducts\Model\ShopPromotionalProductsItem $shopPromotionalProductsItem
	 * @return Status
	 */
	public function addShopPromotionalProductsItem(\Magere\PromotionalProducts\Model\ShopPromotionalProductsItem $shopPromotionalProductsItem) {
		$this->shopPromotionalProductsItem[] = $shopPromotionalProductsItem;

		return $this;
	}

	/**
	 * Remove shopPromotionalProductsItem
	 *
	 * @param \Magere\PromotionalProducts\Model\ShopPromotionalProductsItem $shopPromotionalProductsItem
	 */
	public function removeShopPromotionalProductsItem(\Magere\PromotionalProducts\Model\ShopPromotionalProductsItem $shopPromotionalProductsItem) {
		$this->shopPromotionalProductsItem->removeElement($shopPromotionalProductsItem);
	}

	/**
	 * Get shopPromotionalProductsItem
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getShopPromotionalProductsItem() {
		return $this->shopPromotionalProductsItem;
	}

	/**
	 * Set entity
	 *
	 * @param \Magere\Entity\Model\Entity $entity
	 * @return Status
	 */
	public function setEntity(\Magere\Entity\Model\Entity $entity = null) {
		$this->entity = $entity;

		return $this;
	}

	/**
	 * Get entity
	 *
	 * @return \Magere\Entity\Model\Entity
	 */
	public function getEntity() {
		return $this->entity;
	}


	/**
	 * @return Status[]
	 */
	public function getWorkflow() {
		return $this->workflow;
	}

	/**
	 * @param Status[] $workflow
	 * @return Status
	 */
	public function setWorkflow($workflow) {
		$this->workflow = $workflow;

		return $this;
	}

	/**
	 * @return Rule
	 */
	public function getRule() {
		return $this->rule;
	}

	/**
	 * @param Rule $rule
	 * @return Status
	 */
	public function setRule($rule) {
		$this->rule = $rule;

		return $this;
	}

	/**
	 * @return Progress
	 */
	public function getProgress() {
		return $this->progress;
	}

	/**
	 * @param Progress $progress
	 * @return Status
	 */
	public function setProgress($progress) {
		$this->progress = $progress;

		return $this;
	}

}
