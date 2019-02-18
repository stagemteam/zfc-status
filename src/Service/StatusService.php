<?php
namespace Stagem\ZfcStatus\Service;

use Popov\ZfcEntity\Helper\EntityHelper;
use Popov\ZfcForm\FormElementManager;
use Stagem\ZfcStatus\Helper\StatusHelper;
use Stagem\ZfcStatus\Model\Status;
use Popov\ZfcCore\Service\DomainServiceAbstract;
use Popov\ZfcEntity\Model\Entity;

class StatusService extends DomainServiceAbstract {

	protected $entity = Status::class;

    /**
     * @var StatusHelper
     */
    protected $statusHelper;

    /**
     * @var EntityHelper
     */
	protected $entityHelper;

    /**
     * @var StatusChanger
     */
    protected $statusChanger;

    /**
     * @var FormElementManager
     */
    protected $elementManager;

    public function __construct(
        StatusHelper $statusHelper,
        EntityHelper $entityHelper,
        StatusChanger $statusChanger,
        FormElementManager $elementManager
    )
    {
        $this->statusHelper = $statusHelper;
        $this->entityHelper = $entityHelper;
        $this->statusChanger = $statusChanger;
        $this->elementManager = $elementManager;
    }

    /**
     * @return StatusChanger
     */
    public function getStatusChanger()
    {
        return $this->statusChanger;
    }

    /**
     * @param Entity $entity
     * @return mixed
     */
	public function getStatusesByEntity($entity) {
        $statuses = $this->getRepository()->findBy(['entity' => $entity]);

		return $statuses;
	}

	/**
	 * @param string $entityMnemo
	 * @param string $hidden
	 * @return mixed
	 */
	public function getItemsCollection($entityMnemo = '', $hidden = '')
	{
		/** @var \Stagem\ZfcStatus\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findAll($entityMnemo, $hidden);
	}

	/**
	 * @param string|array $entityMnemo
	 * @param string $mnemo, possible keys: all, empty, notEmpty
	 * @return mixed
	 */
	public function getItems($entityMnemo = '', $mnemo = 'all')
	{
		/** @var \Stagem\ZfcStatus\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findItems($entityMnemo, $mnemo);
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @return mixed
	 */
	public function getItemByName($name, $namespace)
	{
		/** @var \Stagem\ZfcStatus\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findOneItemByName($name, $namespace);
	}

	/**
	 * @param string $statusMnemo
	 * @param string $entityMnemo
	 * @return mixed
	 */
	public function getItemByMnemo($statusMnemo, $entityMnemo)
	{
		/** @var \Stagem\ZfcStatus\Model\Repository\StatusRepository $repository */
		$repository = $this->getRepository($this->_repositoryName);

		return $repository->findOneItemByMnemo($statusMnemo, $entityMnemo);
	}

	public function changeStatus($itemMnemo, $itemId, $statusId, array $data = null)
    {
        /*$itemMnemo = $post->get('item');
        $itemId = $post->get('itemId');
        $statusId = $post->get('status');*/

        //\Zend\Debug\Debug::dump($post); die(__METHOD__);

        #unset($post['buttons']);
        //unset($post['status']);

        $om = $this->getObjectManager();
        $item = ($item = $om->find($itemMnemo, $itemId))
            ? $item
            : $itemMnemo;

        $entity = $this->entityHelper->setContext($item)->getEntity();
        $status = $this->getItemByMnemo($statusId, $entity->getMnemo());

        // @todo: Реалізувати Ініціалізатор який буде ін'єктити об'єкт форми у сервіс.
        //         Тут просто викликати метод $service->getForm()
        //$formName = str_replace('Model', 'Form', $itemMnemo) . 'Form';
        /** @var \Zend\Form\Form $form */
        //$form = $fem->get($formName);
        /** @var \Popov\Invoice\Form\InvoiceForm $form */
        ##$form = $this->statusHelper->getChangeForm($itemMnemo);

        //$fem = $this->getFormElementManager();
        $formName = $this->statusHelper->getFormName($entity);
        $form = $this->elementManager->get($formName);
        $form->bind($item);

        if ($postData = $this->statusHelper->getAppropriateEntityData($form->getName(), $data)) {
            $form->setData($postData);
        }

        // @todo Enable status validation
        ##$this->validatable()->apply($form, $status);

        if ($form->isValid()) {
            /** @var \Stagem\ZfcStatus\Service\StatusChanger $changer */
            $changer = $this->getStatusChanger();
            $changer->/*setModule($module)->*/setItem($item);

            if ($changer->canChangeTo($status)) {
                $oldStatus = $changer->getOldStatus();
                $params = ['newStatus' => $status, 'oldStatus' => $oldStatus, 'context' => $this];

                $this->getEventManager()->trigger('change', $item, $params);
                $this->getEventManager()->trigger('change.' . $status->getMnemo(), $item, $params);

                $changer->changeTo($status);

                $this->getEventManager()->trigger('change.post', $item, $params);
                $this->getEventManager()->trigger('change.' . $status->getMnemo() . '.post', $item, $params);

                // persist only new object (not removed or detached)
                if ($this->entity()->isNew($item)) {
                    $om->persist($item);
                }

                //\Zend\Debug\Debug::dump([$post->get('status'), $item->getStatus()->getMnemo(), $oldStatus->getMnemo()]);
                //die(__METHOD__);

                $om->flush();
            } else {
                $message = 'У вас нет доступа для изменения статуса';
            }
        }
    }

	/**
	 * @param array $data
	 * @param object $oneItem
	 * @return mixed
	 * @deprecated
	 */
	//public function save($data, $oneItem, $locator) {
	public function save($data, $oneItem) {
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

}