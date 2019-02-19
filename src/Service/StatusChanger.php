<?php
/**
 * Global Status Changer
 *
 * @category Popov
 * @package Popov_Invoice
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 25.02.2016 23:31
 */
namespace Stagem\ZfcStatus\Service;

use Popov\ZfcEntity\Helper\ModuleHelper;
use Popov\ZfcForm\FormElementManager;
use Zend\Stdlib\Exception;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

use DateTime;
use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\ORM\Mapping\ClassMetadata;


use Popov\ZfcPermission\Model\PermissionAccess;
use Stagem\ZfcStatus\Service\RuleChecker;
use Stagem\ZfcStatus\Model\Status;
use Popov\ZfcEntity\Model\Entity as Module;

class StatusChanger implements ObjectManagerAwareInterface, EventManagerAwareInterface {

    use ProvidesObjectManager;

    use EventManagerAwareTrait;

    const DEFAULT_STATUS_MNEMO = 'draft';

    /** @var StatusService */
    protected $statusService;

    /** @var RuleChecker */
    protected $ruleHandler;

    /** @var ModuleHelper */
    protected $moduleHelper;

    /** @var array */
    protected $tree;

    /**
     * @var FormElementManager
     */
    protected $elementManager;


    protected $defaultStatus;

    /** @var Module */
    protected $entity;

    protected $item;

    /** @var string $message */
    protected $message;

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

    public function __construct(
        StatusService $statusService,
        ModuleHelper $moduleHelper,
        FormElementManager $elementManager
    ) {
        $this->statusService = $statusService;
        $this->moduleHelper = $moduleHelper;
        $this->elementManager = $elementManager;
    }

    public function getStatusService() {
        return $this->statusService;
    }

    public function getModuleHelper() {
        return $this->moduleHelper;
    }

    public function getEntityHelper() {
        return $this->getModuleHelper()->getEntityHelper();
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
                ->findOneBy(['entity' => $this->getEntity(), 'automatically' => 1]);
        }
        return $this->defaultStatus;
    }

    /**
     * Module for object which will be change status
     *
     * @param Module $module
     * @return $this
     */
    public function setEntity(Module $module) {
        $this->entity = $module;

        return $this;
    }

    public function getEntity() {
        $item = $this->getItem();
        $entity = $this->getEntityHelper()->setContext($item)->getEntity();

        return $entity;
    }

    public function setItem($item) {
        $this->item = $item;
        $this->assignItemWithStatus($item);

        $itemWithStatus = $this->getItemWithStatus();

        // if item has no item then set default status
        $this->oldStatus = ($oldStatus = $itemWithStatus->getStatus())
            ? $oldStatus
            : $itemWithStatus->setStatus($this->getDefaultStatus())->getStatus();

        //\Zend\Debug\Debug::dump([$this->getModule()->getId(), get_class($this->getDefaultStatus())]); die(__METHOD__);

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
     * @todo use EntityPlugin::getMainItemClass()
     */
    public function prepareItemWithStatus($item) {
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
                    $isAssign = $this->prepareItemWithStatus($itemWithStatus);
                    $depth--;

                    if ($isAssign) {
                        $setter = 'set' . ucfirst($field);
                        $itemWithStatus->getId() ? : $item->{$setter}($itemWithStatus);

                        return $isAssign;
                    }
                }
            }
        }

        return false;
    }

    public function assignItemWithStatus($item) {
        if (!$itemWithStatus = $this->prepareItemWithStatus($item)) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot find Status object in "%s" and related objects with relation OneToOne.',
                get_class($item)
            ));
        }

        return $itemWithStatus;
    }

    public function hasItemWithStatus($item)
    {
        return (true == $this->prepareItemWithStatus($item));
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
        //    $settings,
        //    __METHOD__.__LINE__
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

    public function change($itemMnemo, $itemId, $statusId, array $data = null)
    {
        /*$itemMnemo = $post->get('item');
        $itemId = $post->get('itemId');
        $statusId = $post->get('status');*/

        //\Zend\Debug\Debug::dump($post); die(__METHOD__);

        #unset($post['buttons']);
        //unset($post['status']);

        $om = $this->getObjectManager();

        $entityHelper = $this->getEntityHelper();
        $entity = $entityHelper->getBy($itemMnemo, 'mnemo');

        $item = ($item = $om->find($entity->getNamespace(), $itemId))
            ? $item
            : $itemMnemo;

        //$entityHelper = $this->getEntityHelper();
        //$entity = $entityHelper->setContext($item)->getEntity();
        //$status = $this->statusService->getItemByMnemo($statusId, $entity->getMnemo());
        $status = $this->statusService->find($statusId);

        // @todo: Реалізувати Ініціалізатор який буде ін'єктити об'єкт форми у сервіс.
        //         Тут просто викликати метод $service->getForm()
        //$formName = str_replace('Model', 'Form', $itemMnemo) . 'Form';
        /** @var \Zend\Form\Form $form */
        //$form = $fem->get($formName);
        /** @var \Popov\Invoice\Form\InvoiceForm $form */
        ##$form = $this->statusHelper->getChangeForm($itemMnemo);

        ##$formName = $this->getFormName($entity);
        ##$form = $this->elementManager->get($formName);
        ##$form->bind($item);

        ##if ($postData = $this->getAppropriateEntityData($form->getName(), $data)) {
        ##    $form->setData($postData);
        ##}

        // @todo Enable status validation
        ##$this->validatable()->apply($form, $status);

        ##if ($form->isValid()) {
            /** @var \Stagem\ZfcStatus\Service\StatusChanger $this */
            //$changer = $this->getStatusChanger();
            $this->/*setModule($module)->*/setItem($item);

            if ($this->canChangeTo($status)) {
                $oldStatus = $this->getOldStatus();
                $params = ['newStatus' => $status, 'oldStatus' => $oldStatus, 'context' => $this];

                $this->getEventManager()->trigger('change', $item, $params);
                $this->getEventManager()->trigger('change.' . $status->getMnemo(), $item, $params);

                $this->changeTo($status);

                $this->getEventManager()->trigger('change.post', $item, $params);
                $this->getEventManager()->trigger('change.' . $status->getMnemo() . '.post', $item, $params);

                // persist only new object (not removed or detached)
                if ($this->getEntityHelper()->isNew($item)) {
                    $om->persist($item);
                }

                //\Zend\Debug\Debug::dump([$post->get('status'), $item->getStatus()->getMnemo(), $oldStatus->getMnemo()]);
                //die(__METHOD__);

                $om->flush();
            ##} else {
                // @todo throw Exception
            ##    $message = 'У вас нет доступа для изменения статуса';
            ##}
        }
    }

    public function getFormName($entity)
    {
        //$entityName = is_object($entity) ? get_class($entity) : $entity;
        $entityName = $this->getEntityHelper()->getDoctrineClass($entity);
        $formName = str_replace('Model', 'Form', $entityName) . 'Form';

        return $formName;
    }

    /**
     * Get appropriate entity data
     *
     * Some times change action retrieve redundant data.
     * This method find appropriate data in array by entity mnemo.
     *
     * @param $formName
     * @param array $postData
     * @return array|bool
     */
    public function getAppropriateEntityData($formName, $postData)
    {
        //\Zend\Debug\Debug::dump([$formName, $postData]);

        if (isset($postData[$formName])) {
            return [$formName => $postData[$formName]];
        } else {
            foreach ($postData as $name => $value) {
                if (is_array($value) && ($data = $this->getAppropriateEntityData($formName, $value))) {
                    return $data;
                }
            }
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
        /** @var \Stagem\ZfcStatus\Model\Status $status */
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

    public function reset() {
        $this->defaultStatus = null;
        $this->entity = null;
        $this->item = null;
        $this->itemWithStatus = null;
        $this->oldStatus = null;
        $this->newStatus = null;

        return $this;
    }
}