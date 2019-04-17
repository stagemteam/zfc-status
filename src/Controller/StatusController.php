<?php
namespace Stagem\ZfcStatus\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use    Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;

use Stagem\ZfcStatus\Form\Status as StatusForm;

/**
 * Class StatusController
 *
 * @package Stagem\ZfcStatus
 * @method \Stagem\ZfcStatus\Controller\Plugin\Statusable statusable()
 * @method \Stagem\ZfcStatus\Controller\Plugin\Validatable validatable()
 * @method \Stagem\ZfcStatus\Controller\Plugin\StatusPlugin status()
 * @method \Magere\Entity\Controller\Plugin\ModulePlugin module()
 * @method \Magere\Entity\Controller\Plugin\EntityPlugin entity()
 */
class StatusController extends AbstractActionController {

    public $serviceName = 'StatusService';
    public $controllerRedirect = 'status';
    public $actionRedirect = 'index';

    public function indexAction() {
        $locator = $this->getServiceLocator();
        /** @var \Stagem\ZfcStatus\Service\StatusService $service */
        $service = $locator->get($this->serviceName);
        $this->layout('layout/home');

        return [
            'fields' => $service->getFields(),
            'selectedReviews'  => $service->getItemsCollection('', '0'),
        ];
    }

    public function addAction() {
        $this->layout('layout/home');
        $viewModel = new ViewModel();
        $viewModel->setVariables($this->editAction());

        return $viewModel->setTemplate("magere/status/edit.phtml");
    }

    public function editAction() {
        $request = $this->getRequest();
        $route = $this->getEvent()->getRouteMatch();
        $sm = $this->getServiceLocator();
        /** @var \Stagem\ZfcStatus\Service\StatusService $service */
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
        // тоді генерується перехресна матриця зі значиннями цієї сутності, наприклад статусів.
        // Якщо просто одне значення, тоді генерується звичайна додаткова опція.
        $settingsMnemo = ['status', 'change', 'changeWith'];
//        $settingsMnemo = ['cityId'];
        $action = 'checkout-booking/cart'; // перша частина $action має дорівнювати $moduleMnemo інакше не коректно згенерується перехресне налаштування статусів
        $entityMnemo = 'checkout-booking';
//        $moduleMnemo = 'handbook';


        // find one status
        $entity = $sm->get('EntityService')->getOneItem($entityMnemo, 'mnemo');
        $statusTable = 'status';
        $select = $sql->select($statusTable)->where(['entityId' => $entity->getId()]);
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $result->current();
        $status    = $result->next();

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
        //\Zend\Debug\Debug::dump($page); die(__METHOD__);

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

            //\Zend\Debug\Debug::dump([$action, $permissionAction]); die(__METHOD__);

            // find setting in permission
            $select = $sql->select($permissionTable);
            $select->where([
                'target' => $action,
                'type' => $type = 'settings',
                'entityId' => $permissionPageBind['id'],
            ]);
            $permission = $sql->prepareStatementForSqlObject($select)->execute()->current();

            //\Zend\Debug\Debug::dump($permission); //die(__METHOD__);
            
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

        \Zend\Debug\Debug::dump($permissionAction); die(__METHOD__ . __LINE__);



        //$spec = function (\Zend\Db\Sql\Where $where) {
        //    $where->like('username', 'ralph%');
        //    $where->in('pc.cityId', [104, 111, 116]);
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
            /** @var \Stagem\ZfcStatus\Service\StatusService $statusService */
            /** @var \Magere\Permission\Service\PermissionService $permissionService */
            /** @var \Magere\Entity\Service\EntityService $moduleService */
            /** @var \Zend\Stdlib\Parameters $post */
            $sm = $this->getServiceLocator();
            //$fem = $sm->get('FormElementManager');
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
            $item = ($item = $om->find($itemPost, $itemIdPost))
                ? $item
                : $sm->get($itemPost);

            //$module = $this->module()->setRealContext($item)->getRealModule();
            //$status = $statusService->getOneItemByMnemo($statusPost, $module->getMnemo());
            $entity = $this->entity()->setContext($item)->getEntity();
            $status = $statusService->getItemByMnemo($statusPost, $entity->getMnemo());

            // @todo: Реалізувати Ініціалізатор який буде ін'єктити об'єкт форми у сервіс.
            //         Тут просто викликати метод $service->getForm()
            //$formName = str_replace('Model', 'Form', $itemPost) . 'Form';
            /** @var \Zend\Form\Form $form */
            //$form = $fem->get($formName);
            /** @var \Popov\Invoice\Form\InvoiceForm $form */
            $form = $this->status()->getChangeForm($itemPost);
            $form->bind($item);
            if ($postData = $this->status()->getAppropriateEntityData($form->getName(), $post)) {
                $form->setData($postData);
            }

            $this->validatable()->apply($form, $status);

			//\Zend\Debug\Debug::dump(get_class($form)); die(__METHOD__ . __LINE__);
            $message = '';
            if ($form->isValid()) {
                /** @var \Stagem\ZfcStatus\Service\StatusChanger $changer */
                $changer = $sm->get('StatusChanger');
                $changer->/*setModule($module)->*/setItem($item);

                if ($changer->canChangeTo($status)) {
                    $oldStatus = $changer->getOldStatus();
                    $params = ['newStatus' => $status, 'oldStatus' => $oldStatus, 'context' => $this];

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
            } else {
                $asString = function($collection) use (& $asString)
                {
                    static $string = [];
                    foreach ($collection as $key => $row) {
                        if (is_array($row)) {
                            $string[$key] = $asString($row);
                        } else {
                            return [$key => $row];
                        }
                    }
                    return $string;
                };
				
                // not valid form
				//\Zend\Debug\Debug::dump($form->getMessages());
                //$message = join(PHP_EOL, $asString($form->getMessages()));
                $message = json_encode($form->getMessages());
				//$this->flashMessenger()->addErrorMessage($message);
            }
			//\Zend\Debug\Debug::dump($message); die(__METHOD__ . __LINE__);
			
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
            /** @var \Stagem\ZfcStatus\Service\StatusService $service */
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

    /**
     * @param string $entityName
     * @return \Zend\Form\Form
     */
    /*protected function getForm($entityName)
    {
        $sm = $this->getServiceLocator();
        $fem = $sm->get('FormElementManager');

        $formName = str_replace('Model', 'Form', $entityName) . 'Form';
        $form = $fem->get($formName);

        return $form;
    }*/
}