<?php
namespace Agere\Status\Service;

use Agere\Permission\Service\AbstractEntityService;
//use Magere\Logs\Event\Logs as LogsEvent;
use Agere\Status\Model\Status;

use Popov\ZfcCore\Service\DomainServiceAbstract;

class StatusService extends DomainServiceAbstract {

	protected $entity = Status::class;
	protected $repository = Status::class;

	protected $_repositoryName = 'status';
	protected $_entityAlias = 'Status';

	/** @var Module */
	protected $module;

	/**
	 * @return Module
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * @param Module $module
	 * @return StatusService
	 */
	public function setModule($module)
	{
		$this->module = $module;

		return $this;
	}
	/**
	 * @var array
	 */
	protected $_fields = [
		'id'			=> '№',
		'status'		=> 'Статус',
		'category'		=> 'Категория',
	];

	public function save(Status $status)
	{
		$om = $this->getObjectManager();
		if (!$om->contains($status)) {
			$om->persist($status);
		}
		$om->flush();
	}
	
	
	
	public function getStatusAutomaticallyByModule() {
		//$module = $this->getModule();
		$status = $this->getRepository()->getStatus();
		
		return $status;
	}
	
	/*===========================Old Code===========================*/
	public function getConfigStatuses($namespace) {
		$config = $this->getServiceManager()->get('Config');
		$statusMnemo = array_keys($config['status']['validation'][$namespace]);


		//$om = $this->getObjectManager();

		$repo = $this->getRepository();
		$statuses = $repo->findBy(['mnemo' => $statusMnemo]);

		/*$qb = $repo->createQueryBuilder('status');
		$qb->where($qb->expr()->andx(
			$qb->expr()->in('status.mnemo', '?1')
		))->setParameter(1, $statusMnemo); // hardcode for RAZ #2*/
		//$qb->orderBy('city.priority', 'DESC');
		//$statuses = $qb->getQuery()->getResult();

		return $statuses;
	}


	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * @param string $entityMnemo
	 * @param string $hidden
	 * @return mixed
	 */
	public function getItemsCollection($entityMnemo = '', $hidden = '')
	{
		/** @var \Agere\Status\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository();
		return $repository->findAll($entityMnemo, $hidden);
	}

	/**
	 * @param string|array $entityMnemo
	 * @param string $mnemo, possible keys: all, empty, notEmpty
	 * @return mixed
	 */
	public function getItems($entityMnemo = '', $mnemo = 'all')
	{
		/** @var \Magere\Status\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository();

		return $repository->findItems($entityMnemo, $mnemo);
	}

	/**
	 * @param int $id
	 * @param string $field
	 * @return mixed
	 */
	public function getOneItem($id, $field = 'id')
	{
		/** @var \Magere\Status\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findOneItem($id, $field);
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @return mixed
	 */
	public function getOneItemByName($name, $namespace)
	{
		/** @var \Magere\Status\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findOneItemByName($name, $namespace);
	}

	/**
	 * @param string $statusMnemo
	 * @param string $entityMnemo
	 * @return mixed
	 */
	public function getOneItemByMnemo($statusMnemo, $entityMnemo)
	{
		/** @var \Magere\Status\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findOneItemByMnemo($statusMnemo, $entityMnemo);
	}

	/**
	 * @param array $data
	 * @param object $oneItem
	 * @return mixed
	 * @deprecated
	 */
	//public function save($data, $oneItem, $locator) {
	public function saveOld($data, $oneItem) {
		$isNew = false;
		// Set default values
		if (is_null($oneItem->getId())) {
			$data['remove'] = '1';
			if (!isset($data['automatically'])) {
				$data['automatically'] = 0;
			}
			if (!isset($data['color'])) {
				$data['color'] = '';
			}
			$isNew = true;
		}
		// END Set default values

		unset($data['id']);
		/** @var \Magere\Entity\Service\EntityService $entityService */
		$entityService = $this->getServiceLocator()->get('EntityService');
		foreach ($data as $field => $val) {
			if ($field == 'namespace') {
				$obj = $entityService->getOneItem($val, $field);
				$oneItem->setEntity($obj);
				$oneItem->setEntityId($obj->getId());
			} else {
				$method = 'set' . ucfirst($field);
				$oneItem->$method($val);
			}
		}
		$repository = $this->getRepository($this->_repositoryName);
		$repository->save($oneItem);
		if ($isNew) {
			// Update Module Permission
			$this->updatePermission(__CLASS__);
		}

		return $oneItem;
	}

	/**
	 * @param $id
	 * @return bool
	 * @deprecated
	 */
	public function deleteItem($id)
	{
		$oneItem = $this->getOneItem($id);

		if ($oneItem->getId() && $oneItem->getRemove())
		{
			/** @var \Magere\Store\Service\StoreService $storeService */
			$storeService = $this->getServiceLocator()->get('StoreService');
			$storeItem = $storeService->getAllItemsCollection(['carStatusId' => $id], 1);

			/** @var \Magere\Mail\Service\MailService $mailService */
			$mailService = $this->getServiceLocator()->get('MailService');
			$mailItem = $mailService->getOneItem($id, 'statusId');

			/** @var \Magere\Documents\Service\DocumentsService $documentsService */
			$documentsService = $this->getServiceLocator()->get('DocumentsService');
			$documentsItem = $documentsService->getOneItem($id, 'statusId');

			$orderSaleItem = $this->getRepositoryAlias('OrderSale')->findOneBy(['statusId' => $id]);

			/** @var \Magere\Buyer\Service\BuyerService $buyersService */
			$buyersService = $this->getServiceLocator()->get('BuyersService');
			$buyersItem = $buyersService->getOneItem($id, 'statusId');

			/** @var \Magere\Clients\Service\ClientsService $clientsService */
			$clientsService = $this->getServiceLocator()->get('ClientsService');
			$clientsItem = $clientsService->getOneCollectionBy(['statusId' => $id]);

			/** @var \Magere\Logistics\Service\LogisticsService $logisticsService */
			$logisticsService = $this->getServiceLocator()->get('LogisticsService');
			$logisticsItem = $logisticsService->getOneItem($id, 'statusId');
			$logisticsItemWith = $logisticsService->getOneItem($id, 'statusIdWith');

            /** @var \Magere\Spares\Service\ShopSparesService $shopSparesService */
            $shopSparesService = $this->getServiceLocator()->get('ShopSparesService');
            $shopSparesItem = $shopSparesService->getOneCollectionBy(['statusId' => $id]);

            /** @var \Magere\Spares\Service\ShopSparesItemService $shopSparesItemService */
            $shopSparesItemService = $this->getServiceLocator()->get('ShopSparesItemService');
            $shopSparesItemChild = $shopSparesItemService->getOneCollectionBy(['statusId' => $id]);

			if (! $storeItem && ! $mailItem->getId() && ! $documentsItem->getId() && is_null($orderSaleItem)
				&& ! $buyersItem->getId() && ! $clientsItem->getId() && ! $logisticsItem->getId()
                && ! $logisticsItemWith->getId() && ! $shopSparesItem->getId() && ! $shopSparesItemChild->getId())
			{
				$repository = $this->getRepository($this->_repositoryName);
				$repository->delete($oneItem);

				return true;
			}
		}

		return false;
	}


	//------------------------------------------Events------------------------------------------
	/**
	 * Module Users
	 *
	 * @param $class
	 * @param $params
	 * @return mixed
	 */
	public function delete($class, $params)
	{
		$event = new LogsEvent();
		return $event->events($class)->trigger('status.delete', $this, $params);
	}

	/**
	 * Module Permission
	 *
	 * @param $class
	 * @param $params
	 */
	public function updatePermission($class, $params = [])
	{
		$event = new LogsEvent();
		$event->events($class)->trigger('status.updatePermission', $this, $params);
	}

}