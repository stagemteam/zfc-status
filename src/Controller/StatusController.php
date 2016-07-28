<?php
namespace Agere\Status\Controller;

use Agere\Status\Service\StatusService;
use Zend\Mvc\Controller\AbstractActionController;
use	Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;

use Agere\Status\Form\StatusForm;

/**
 * Class StatusController
 *
 * @package Magere\Status
 * @method \Magere\Status\Controller\Plugin\Statusable statusable()
 * @method \Magere\Status\Controller\Plugin\Validatable validatable()
 * @method \Magere\Entity\Controller\Plugin\Module module()
 */
class StatusController extends AbstractActionController {

	public $serviceName = 'StatusService';
	public $controllerRedirect = 'status';
	public $actionRedirect = 'index';


	public function indexAction()
	{
		//$sm = $this->getServiceManager();
		$sm = $this->getServiceLocator();
		$service = $sm->get($this->serviceName);
		//$users = $this->getService()->getRepository()->findByRoles(1);
		$statutes = $service->getRepository()->getStatuses();

		/** @var StatusGrid $statusGrid */
		$statusGrid = $sm->get('StatusGrid');
		$statusDataGrid = $statusGrid->getDataGrid();
		$statusDataGrid->setDataSource($statutes);
		$statusDataGrid->render();
		$statusDataGridVm = $statusDataGrid->getResponse();

		return $statusDataGridVm;
	}

	public function createAction()
	{
		return $viewModel = $this->editAction();
	}

	function editAction()
	{
		$request = $this->getRequest();
		$route = $this->getEvent()->getRouteMatch();
		$service = $this->getService();
		$fm = $this->getServiceLocator()->get('FormElementManager');
		/** @var Status $status */
		$status = ($status = $service->find($id = (int) $route->getParam('id')))
			? $status
			: $service->getObjectModel();

		/** @var StatusForm $form */
		$form = $fm->get(StatusForm::class);

		$form->bind($status);
		if ($request->isPost()) {
			$form->setData($request->getPost());
			if ($form->isValid()) {
				$this->getService()->save($status);
				$msg = 'Статус был успешно сохранен';
				$this->flashMessenger()->addSuccessMessage($msg);

				return $this->redirect()->toRoute('default', array (
					'controller' => 'status',
					'action'     => 'index',
				));

			} else {
				$msg = 'Форма не валидна. Проверьте значение и внесите коррективы';
				$this->flashMessenger()->addSuccessMessage($msg);
			}
		}

		return new ViewModel([
			'form' => $form,
		]);
	}

	/**
	 * @return StatusService
	 */
	public function getService()
	{
		return  $this->getServiceLocator()->get('StatusService');
	}

/*===================Old code======================*/

	public function indexAction2() {
		$locator = $this->getServiceLocator();
		/** @var \Magere\Status\Service\StatusService $service */
		$service = $locator->get($this->serviceName);
		//$this->layout('layout/home');

		/*$items = $service->getItemsCollection('', '0');
		$fields =  $service->getFields();
		foreach ($items as $item) {
			\Zend\Debug\Debug::dump($item); die();

		}*/


		return [
			'fields' => $service->getFields(),
			'items'  => $service->getItemsCollection('', '0'),
		];
	}

	public function addAction() {
		$this->layout('layout/home');
		$viewModel = new ViewModel();
		$viewModel->setVariables($this->editAction());

		return $viewModel->setTemplate("magere/status/edit.phtml");
	}

	public function editAction2() {
		$request = $this->getRequest();
		$route = $this->getEvent()->getRouteMatch();
		$sm = $this->getServiceLocator();
		/** @var \Magere\Status\Service\StatusService $service */
		$service = $sm->get($this->serviceName);
		$id = (int) $route->getParam('id');
		$item = $service->getOneItem($id);
		$form = new StatusForm($id, $sm->get('Zend\Db\Adapter\Adapter'));
		$fields = ['entityId', 'name'];
		foreach ($fields as $field) {
			$method = 'get' . ucfirst($field);
			$form->get($field)->setValue($route->getParam($field, $item->$method()));
		}
		if ($request->isPost()) {
			$post = $request->getPost()->toArray();
			$form->setData($post);
			if ($form->isValid()) {
				/** @var \Magere\Entity\Service\EntityService $serviceEntity */
				$serviceEntity = $sm->get('EntityService');
				$post = $form->getData();
				$saveData = [];
				foreach ($fields as $field) {
					if ($field == 'entityId') {
						$saveData[rtrim($field, 'Id')] = $serviceEntity->getOneItem($post[$field]);
					} else {
						$saveData[$field] = $post[$field];
					}
				}
				if ($saveData) {
					$saveData['id'] = $id;
					if (!$id) {
						$saveData['mnemo'] = '';
						$saveData['hidden'] = '0';
					}
					//$service->save($saveData, $item, $sm);
					$service->save($saveData, $item);
				}
				$this->redirect()->toRoute('default', [
					'controller' => $this->controllerRedirect,
					'action'     => $this->actionRedirect,
				]);
			}
		}
		$this->layout('layout/home');

		return [
			'id'     => $item->getId(),
			'fields' => $service->getFields(),
			'form'   => $form,
		];
	}

	/**
	 * Дає можливість відобразити вертикальний перелік статусів
	 * на панелі налаштувань доступів.
	 *
	 * Важливо: Спочатку запустити оновлення контролерів
	 */
	public function addPermissionSettingsAction() {
		$sm = $this->getServiceLocator();
		/** @var Adapter $dbAdapter */
		$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		$sql = new Sql($dbAdapter);


		// Прив'язані налаштування.
		// Якщо декілька значень прив'язаних до однієї сутності,
		// тоді генерується перехресна матриця зі значиннями цієї сутності,
		// наприклад статусів.
		// Якщо просто одне значення, тоді генерується звичайна додаткова опція.
		$settingsMnemo = ['status', 'change', 'changeWith'];
		//$settingsMnemo = ['cityId'];
		$action = 'discount/card';
		$moduleMnemo = 'discount';
		//$moduleMnemo = 'handbook';


		// find one status
		$module = $sm->get('EntityService')->getOneItem($moduleMnemo, 'mnemo');
		$statusTable = 'status';
		$select = $sql->select($statusTable)->where(['entityId' => $module->getId()]);
		$result = $sql->prepareStatementForSqlObject($select)->execute();
		$result->current();
		$status	= $result->next();

		//\Zend\Debug\Debug::dump($status); die(__METHOD__);

		// prepare pages table
		$pageTable = 'pages';
		$select = $sql->select($pageTable)->where(['page' => $action]);
		$page = $sql->prepareStatementForSqlObject($select)->execute()->current();
		if (!$page) {
			$insert = $sql->insert($pageTable)->values(['page' => $action]);
			/** @var \Zend\Db\Adapter\Driver\Pdo\Result $results */
			$results = $sql->prepareStatementForSqlObject($insert)->execute();
			$select = $sql->select($pageTable)->where(['id' => $results->getGeneratedValue()]);
			$results = $sql->prepareStatementForSqlObject($select)->execute();

			$page = $results->current();
		}

		// bind settings to page
		$permissionSettingTable = 'permission_settings';
		$select = $sql->select($permissionSettingTable);
		$select->where->in('mnemo', $settingsMnemo);
		$settings = $sql->prepareStatementForSqlObject($select)->execute();

		$firstPermissionBind = [];
		$permissionSettingsPagesTable = 'permission_settings_pages';
		foreach ($settings as $setting) {
			$select = $sql->select($permissionSettingsPagesTable);
			$select->reset($select::WHERE)->where([
				'permissionSettingsId' => $setting['id'],
				'pagesId' => $page['id']
			]);

			$settingToPage = $sql->prepareStatementForSqlObject($select)->execute()->current();

			// add bunch of $settingToPage
			if (!$settingToPage) {
				$insert = $sql->insert($permissionSettingsPagesTable)->values([
					'permissionSettingsId' => $setting['id'],
					'pagesId' => $page['id']
				]);
				/** @var \Zend\Db\Adapter\Driver\Pdo\Result $results */
				$results = $sql->prepareStatementForSqlObject($insert)->execute();
				$select = $sql->select($permissionSettingsPagesTable)->where(['id' => $results->getGeneratedValue()]);
				$settingToPage = $sql->prepareStatementForSqlObject($select)->execute()->current();
			}

			$permissionSettingsPagesId = $firstPermissionBind ? $firstPermissionBind['permissionSettingsPagesId'] : $settingToPage['id'];
			$entityId = $status['id']; // @todo поставити 0 якщо  permission_settings.entityId = NULL
			$childrenId = $firstPermissionBind ? $settingToPage['id'] : 0;

			// bind setting with status
			$permissionPageBindTable = 'permission_page_bind';
			$select = $sql->select($permissionPageBindTable);
			$select->reset($select::WHERE)->where([
				'permissionSettingsPagesId' => $permissionSettingsPagesId,
				'entityId' => $entityId,
				'childrenId' => $childrenId,
			]);

			$permissionPageBind = $sql->prepareStatementForSqlObject($select)->execute()->current();

			if (!$permissionPageBind) {
				$insert = $sql->insert($permissionPageBindTable)->values([
					'permissionSettingsPagesId' => $permissionSettingsPagesId,
					'entityId' => $entityId,
					'childrenId' => $childrenId,
				]);

				//$SqlString = $sql->buildSqlString($insert);
				//\Zend\Debug\Debug::dump($SqlString); //die(__METHOD__);

				/** @var \Zend\Db\Adapter\Driver\Pdo\Result $results */
				$results = $sql->prepareStatementForSqlObject($insert)->execute();
				$select = $sql->select($permissionPageBindTable)->where(['id' => $results->getGeneratedValue()]);
				$permissionPageBind = $sql->prepareStatementForSqlObject($select)->execute()->current();
			}

			if (!$firstPermissionBind) {
				$firstPermissionBind = $permissionPageBind;
			}

			// bind permission and setting
			$permissionTable = 'permission';
			$select = $sql->select($permissionTable);
			$select->where([
				'target' => $action,
				'type' => 'action',
			]);
			$permissionAction = $sql->prepareStatementForSqlObject($select)->execute()->current();

			if (!$permissionAction) {
				throw new \Zend\Stdlib\Exception\RuntimeException(sprintf('Action %s not found in table %s. Run generate url before.', $action, $permissionTable));
			}


			// find setting in permission
			$select = $sql->select($permissionTable);
			$select->where([
				'target' => $action,
				'type' => $type = 'settings',
				'entityId' => $permissionPageBind['id'],
			]);
			$permission = $sql->prepareStatementForSqlObject($select)->execute()->current();

			if (!$permission) {
				$insert = $sql->insert($permissionTable)->values([
					'target' => $action,
					'type' => $type,
					'entityId' => $permissionPageBind['id'],
					'module' => $permissionAction['module'],
					'parent' => $permissionAction['parent'],
					'typeField' => $permissionAction['typeField'],
					'required' => $permissionAction['required'],
				]);
				$sql->prepareStatementForSqlObject($insert)->execute();
			}

			//$SqlString = $sql->buildSqlString($insert);
			//\Zend\Debug\Debug::dump($SqlString); //die(__METHOD__);

			/** @var \Zend\Db\Adapter\Driver\Pdo\Result $results */
			//$results = $sql->prepareStatementForSqlObject($insert)->execute();
			//$select = $sql->select($permissionTable)->where(['id' => $results->getGeneratedValue()]);
			//$permissionPageBind = $sql->prepareStatementForSqlObject($select)->execute()->current();


			//\Zend\Debug\Debug::dump($permissionPageBind); die(__METHOD__ . __LINE__);

		}

		\Zend\Debug\Debug::dump($permissionAction); die(__METHOD__);



		//$spec = function (\Zend\Db\Sql\Where $where) {
		//	$where->like('username', 'ralph%');
		//	$where->in('pc.cityId', [104, 111, 116]);
		//};
		//$select->where(new \Zend\Db\Sql\Predicate\In('pc.cityId', [104,111,116]));
		//$select->where($spec);

		//$statement = $sql->prepareStatementForSqlObject($select);
		//$resultSet = $statement->execute();
		//$SqlString = $sql->buildSqlString($select);
		//\Zend\Debug\Debug::dump($SqlString); die(__METHOD__);

	}

	//------------------------------------AJAX----------------------------------------
	/**
	 * Ajax change status.
	 *
	 * Global action for change status.
	 * Ajax request example:
	 */
	public function changeAction() {
		$request = $this->getRequest();
		if ($request->isPost() && $request->isXmlHttpRequest()) {
			/** @var \Magere\Status\Service\StatusService $statusService */
			/** @var \Magere\Permission\Service\PermissionService $permissionService */
			/** @var \Magere\Entity\Service\EntityService $moduleService */
			/** @var \Zend\Stdlib\Parameters $post */
			$sm = $this->getServiceLocator();
			$fem = $sm->get('FormElementManager');
			$om = $sm->get('Doctrine\ORM\EntityManager');
			$statusService = $sm->get('StatusService');
			//$moduleService = $sm->get('EntityService');
			//$permissionService = $sm->get('PermissionService');

			$post = $request->getPost();
			//$config = $sm->get('Config');
			/** @var Current $current */
			//$current = $pm->get('current');

			//$itemPost = $post->get('buttons') ? $post->get('buttons')['item'] : $post->get('item');
			//$itemIdPost = $post->get('buttons') ? $post->get('buttons')['itemId'] : $post->get('itemId');
			//$statusPost = $post->get('status');
			$itemPost = $post->get('item');
			$itemIdPost = $post->get('itemId');
			$statusPost = $post->get('status');


			//\Zend\Debug\Debug::dump($post); die(__METHOD__);

			#unset($post['buttons']);
			unset($post['status']);

			// @todo: Діставати сутність з сервісу через $item = $service->find($itemIdPost);
			$item = $om->find($itemPost, $itemIdPost);

			// @todo: Реалізувати Ініціалізатор який буде ін'єктити об'єкт форми у сервіс.
			// 		Тут просто викликати метод $service->getForm()
			$formName = str_replace('Model', 'Form', $itemPost) . 'Form';
			/** @var \Zend\Form\Form $form */
			$form = $fem->get($formName);

			$form->bind($item);

			if (count($post)) {
				$form->setData($post); // @FIXME
                //\Zend\Debug\Debug::dump($post); die(__METHOD__);
			}

			$this->validatable()->apply($form);

			//\Zend\Debug\Debug::dump($form->getValidationGroup());

			//$formOrElement = $form->get('invoice')->get('invoiceProducts')->getTargetElement()->get('quantityItems')->getTargetElement();
			/** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject $hydrator */
			//$hydrator = $formOrElement->getHydrator();

			//foreach ($hydrator->getStrategy('*') as $strategy) {
			//	\Zend\Debug\Debug::dump([get_class($strategy)]);
			//}

            /*foreach ($hydrator->strategies as $key => $value) {
                \Zend\Debug\Debug::dump([$key, get_class($value)]); //die(__METHOD__);
            }

            \Zend\Debug\Debug::dump([
                //get_class($hydrator->getStrategy('quantityItem')),
                get_class($formOrElement->getHydrator()),
                get_class($formOrElement),
            ]);
            die(__METHOD__);*/
			///\Zend\Debug\Debug::dump([$formOrElement->getName(), get_class($targetElement->getHydrator()), $config]); die(__METHOD__);


			if ($form->isValid()) {
			//if (true) {
				//\Zend\Debug\Debug::dump($post);
				//\Zend\Debug\Debug::dump($form->getValidationGroup());
				//\Zend\Debug\Debug::dump('is valid!'); die(__METHOD__);


				#$invoiceProducts = $om->getRepository($itemPost . 'Product')->findBy(['invoice' => $item]);

                //\Zend\Debug\Debug::dump(count($invoiceProducts), 'count($invoiceProducts)');
				/*foreach ($item->getInvoiceProducts() as $invoiceProduct) {
				//foreach ($invoiceProducts as $invoiceProduct) {
					foreach ($invoiceProduct->getQuantityItems() as $quantityItem) {
						\Zend\Debug\Debug::dump($quantityItem->getId(), '$quantityItem->getId()');
						\Zend\Debug\Debug::dump($quantityItem->getQuantity(), '$quantityItem->getQuantity()');
						\Zend\Debug\Debug::dump($quantityItem->getItem()->getId(), '$quantityItem->getItem()->getId()');
						\Zend\Debug\Debug::dump($quantityItem->getStatus()->getMnemo(), '$quantityItem->getStatus()->getMnemo()');
						\Zend\Debug\Debug::dump('------------');
					}
					\Zend\Debug\Debug::dump('*********************************');
				}

				die(__METHOD__);*/


				/*\Zend\Debug\Debug::dump(get_class($item = $form->getData()));
				foreach ($item->getInvoiceProducts() as $invoiceProduct) {
					\Zend\Debug\Debug::dump($invoiceProduct->getId(), '$invoiceProduct->getId()');
					\Zend\Debug\Debug::dump(count($invoiceProduct->getQuantityItems()));
					\Zend\Debug\Debug::dump($invoiceProduct->getQuantityItems()->first()->getId(), '$invoiceProduct->getQuantityItems()->first()->getId()');
					\Zend\Debug\Debug::dump($invoiceProduct->getQuantityItems()->last()->getId(), '$invoiceProduct->getQuantityItems()->last()->getId()');
					\Zend\Debug\Debug::dump($post);
					die(__METHOD__);
				}*/

				/** @var \Doctrine\ORM\Mapping\ClassMetadata $class */
				#$class = $om->getMetadataFactory()->getMetadataFor($itemPost);
				#$moduleName = $class->isInheritanceTypeSingleTable()
				#	? $this->current()->currentModule(get_parent_class($itemPost))
				#	: $this->current()->currentModule($itemPost);

				//\Zend\Debug\Debug::dump($moduleName, '$moduleName'); die(__METHOD__);

				//$module = $moduleService->getOneItem('Magere\Status', 'namespace');
				#$module = $moduleService->getOneItem($moduleName, 'namespace');
				$module = $this->module()->setRealContext($item)->getModule();;

                $status = $statusService->getOneItemByMnemo($statusPost, $module->getMnemo());


				//$tree = $permissionService->getHumanReadablePermissionsTree($module, $this->user()->current());



				//die(__METHOD__);

				/*\Zend\Debug\Debug::dump([
					'$module=' . get_class($module),
					'$item=' . get_class($item),
					'$post->get("status")=' . $post->get('status'),
					'$status->getMnemo()=' . $status->getMnemo(),
					'$item->getStatus()->getMnemo()=' . $item->getStatus()->getMnemo(),
					//'$oldStatus->getMnemo()=' . $oldStatus->getMnemo(),
					__METHOD__.__LINE__
				]);*/

				/** @var \Magere\Status\Service\StatusChanger $changer */
				$message = '';
				$changer = $sm->get('StatusChanger');
				$changer->setModule($module)->setItem($item);


				if ($changer->canChangeTo($status)) {
					$oldStatus = $changer->getOldStatus();
					$params = ['newStatus' => $status, 'oldStatus' => $oldStatus];

					$this->getEventManager()->trigger('change', $item, $params);
					$this->getEventManager()->trigger('change.' . $status->getMnemo(), $item, $params);

					$changer->changeTo($status);

					$this->getEventManager()->trigger('change.post', $item, $params);
					$this->getEventManager()->trigger('change.' . $status->getMnemo() . '.post', $item, $params);

					/*\Zend\Debug\Debug::dump([
						'$post->get("status")' . $post->get('status'),
						'$status->getMnemo()' . $status->getMnemo(),
						'$item->getStatus()->getMnemo()' . $item->getStatus()->getMnemo(),
						'$oldStatus->getMnemo()' . $oldStatus->getMnemo(),
					]); die(__METHOD__.__LINE__); die(__METHOD__);*/

					// save to db
					if(!$om->contains($item)) {
						$om->persist($item);
					}
					//\Zend\Debug\Debug::dump([$post->get('status'), $item->getStatus()->getMnemo(), $oldStatus->getMnemo()]); die(__METHOD__.__LINE__);
					$om->flush();
				} else {
					$message = 'У вас нет доступа для изменения статуса';
				}
			}
            else {
                $asString = function($collection) use (& $asString) {
                    static $string = '';

                    foreach ($collection as $key => $row) {
                        if (is_array($row)) {
                            $asString($row);
                        } else {
                            $string .= $row;
                        }
                    }

                    return $string;
                };
				// not valid form
                $message = $asString($form->getMessages());
                //\Zend\Debug\Debug::dump($message); die(__METHOD__);
            }



            /*\Zend\Debug\Debug::dump([
                $changer->getItemWithStatus()->getStatus()->getMnemo(),
                $oldStatus->getMnemo(),
                get_class($item->getInvoiceAcceptance())
            ]); die(__METHOD__);*/


			$result = new JsonModel([
				'message' => $message,
			]);

			return $result;
		} else {
			$this->getResponse()->setStatusCode(404);
		}
	}

	/**
	 * Ajax delete
	 */
	public function deleteAction() {
		$request = $this->getRequest();
		if ($request->isPost() && $request->isXmlHttpRequest()) {
			$locator = $this->getServiceLocator();
			/** @var \Magere\Status\Service\StatusService $service */
			$service = $locator->get($this->serviceName);

			// Access to page for current user
			$responseEvent = $service->delete(__CLASS__, []);
			$message = $responseEvent->first()['message'];
			// END Access to page for current user

			if ($message == '') {
				$allow = false;
				$post = $request->getPost();
				$allow = $service->deleteItem($post['id']);
				$result = new JsonModel([
					'message' => ($allow)
                        ? ''
                        : 'Невозможно удалить № ' . $post['id'] . '. Сначала уберите прив\'язку к позиции!',
				]);
			} else {
				$result = new JsonModel([
					'message' => $message,
				]);
			}

			return $result;
		} else {
			$this->getResponse()->setStatusCode(404);
		}
	}

}