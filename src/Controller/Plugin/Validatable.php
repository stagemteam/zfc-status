<?php
/**
 * Plugin which apply validation group relative to status.
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 11.05.16 2:21
 */

namespace Popov\ZfcStatus\Controller\Plugin;

use Magere\AuthorizedPersons\Form\Validator\AuthorizedPersonCondition;
use Zend\Form\FieldsetInterface;
use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\FormInterface;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\Element;
use Zend\Form\Element\Collection as FormCollection;
use Zend\Validator\ValidatorPluginManager;

use Magere\Users\Acl\Acl;
use Magere\Entity\Service\EntityService as ModuleService;
use Popov\ZfcStatus\Service\StatusChanger;
use Popov\ZfcStatus\Form\ButtonFieldset;
use Popov\ZfcStatus\Model\Status;
use Magere\Permission\Service\PermissionService;
use Magere\Users\Controller\Plugin\User as UserPlugin;
use Popov\Simpler\Plugin\SimplerPlugin;
use Magere\Fields\Service\FieldsService;

use Popov\Current\Plugin\Current;

class Validatable extends AbstractPlugin
{
    /**
     * Status has two mode for validation
     * - view: validation based on current item status
     * - change: validation based on applied (click button) status
     */
    const MODE_VIEW = 'view';
    const MODE_CHANGE = 'change';

    /**
     * Default validation mode
     *
     * @var string
     */
    protected $mode = self::MODE_VIEW;

    /** @var [] */
    protected $config;

    /** @var [] */
    protected $permissionTree;

    /** @var ValidatorPluginManager */
    protected $validatorManager;

    protected $userPlugin;

    /** @var SimplerPlugin */
    protected $simplerPlugin;

    /** @var Current */
    protected $current;

    /** @var Statusable */
    protected $statusable;

    /** @var FieldsService $fieldsService */
    protected $fieldsService;

    /** @var Form */
    protected $form;

    /** @var Status */
    protected $status;

    public function __construct(array $config)
    {
        $this->config = $config;
        //$this->fieldsService = $fieldsService;
        //$this->permissionTree = $permissionService;
        #if (!$cpm->get('user')->isAdmin()) {
        #    $tree = $permissionService->getHumanReadablePermissionTree($module, $user);
        #    $changer->setPermissionTree($tree[$module->getId()]);
        #}
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setConfig($config) {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setUserPlugin($userPlugin)
    {
        $this->userPlugin = $userPlugin;

        return $this;
    }

    public function getUserPlugin()
    {
        if (!$this->userPlugin) {
            $this->userPlugin = $this->getController()->plugin('user');
        }
        return $this->userPlugin;
    }

    public function setValidatorManager($validatorManager)
    {

        $this->validatorManager = $validatorManager;

        return $this;
    }

    public function getValidatorManager()
    {
        if (!$this->validatorManager) {
            $this->validatorManager = $this->getController()->getServiceLocator()->get('ValidatorManager');
        }
        return $this->validatorManager;
    }

    /**
     * @param SimplerPlugin $simplerPlugin
     */
    public function setSimplerPlugin(SimplerPlugin $simplerPlugin)
    {
        $this->simplerPlugin = $simplerPlugin;
    }

    /**
     * @return SimplerPlugin
     */
    public function getSimplerPlugin()
    {
        if (!$this->simplerPlugin) {
            $this->simplerPlugin = $this->getController()->plugin('simpler');
        }
        return $this->simplerPlugin;
    }

    public function setPermissionTree($permissionTree)
    {
        $this->permissionTree = $permissionTree;

        return $this;
    }

    public function getPermissionTree()
    {
        return $this->permissionTree;
    }

    public function setFieldsService($fieldsService)
    {
        $this->fieldsService = $fieldsService;

        return $this;
    }

    public function getFieldsService()
    {
        return $this->fieldsService;
    }

    public function getForm() {
        return $this->form;
    }

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function getStatus() {
        if (!$this->status) {
            $form = $this->getForm();
            $statusable = $this->getStatusable();

            if (!$statusable->hasStatus($form->getObject())) {
                return false;
            }

            $this->status = $statusable->getStatus($form->getObject());
            $this->mode = self::MODE_VIEW;
        }

        return $this->status;
    }

    public function setStatus($status)
    {
        if ($status) {
            $this->status = $status;
            $this->mode = self::MODE_CHANGE;
        }
    }

    public function setCurrent($current)
    {
        $this->current = $current;
    }

    public function getCurrent()
    {
        if (!$this->current) {
            $this->current = $this->getController()->current();
        }

        return $this->current;
    }

    public function setStatusable($statusable)
    {
        $this->statusable = $statusable;
    }

    public function getStatusable()
    {
        if (!$this->statusable) {
            $this->statusable = $this->getController()->statusable();
        }

        return $this->statusable;
    }


    /*protected function checkStatus($status)
    {
        if (!is_object($status)) {
            $module = $this->module()->setRealContext($item)->getModule();
            $status = $statusService->getOneItemByMnemo($statusPost, $module->getMnemo());
        }
    }*/

    protected function applyFieldsOld($formOrFieldset)
    {
        $permissionTree = $this->getPermissionTree();
        foreach ($formOrFieldset as $sub) {
            $targetElement = ($sub instanceof FormCollection)
                ? $sub->getTargetElement()
                : $sub;

            if ($targetElement instanceof Fieldset) {
                $controller = $this->getController();
                if ($entity = $controller->entity($targetElement->getObject())->getEntity()) {
                    $fields = $controller->simpler($this->getFieldsService()->getAllByEntity($entity))->asArrayValue('id', 'mnemo');

                    $this->fields[lcfirst($controller->module()->toAlias($entity->getMnemo()))] = $fields;
                }
                $this->applyFields($targetElement);
            } elseif ($targetElement instanceof Element) {
                if (isset($this->fields[$formOrFieldset->getName()])) {
                    $permissionTree = $this->getPermissionTree()['field']['fields'];
                    $fieldsetFields = $this->fields[$formOrFieldset->getName()];

                    // set permission if relative is save in database
                    if (isset($fieldsetFields[$targetElement->getName()]) // @todo I don't know why but without this check notice is generated
                        && isset($permissionTree[$fieldsetFields[$targetElement->getName()]])) {
                        $permission = $permissionTree[$fieldsetFields[$targetElement->getName()]];
                        if (Acl::ACCESS_READ == $permission) {
                            $targetElement->setAttribute('readonly', true);
                        } elseif (Acl::ACCESS_WRITE == $permission) {
                            $targetElement->removeAttribute('readonly');
                        }
                    }
                }
            }

        }
    }

    public function applyStatus()
    {
        /** @var Form $form */
        $form = $this->getForm();
        $config = $this->getConfig();
        $current = $this->getCurrent();
        #$statusable = $this->getStatusable();
        $namespace = $current->currentModule($form->getObject());

        #if (!$statusable->hasStatus($form->getObject())) {
        #    return;
        #}
        #$status = $statusable->getStatus($form->getObject());

        if (!($status = $this->getStatus())) {
            return;
        }

        $mode = $this->getMode();
        //\Zend\Debug\Debug::dump($namespace); die(__METHOD__);
        //\Zend\Debug\Debug::dump($status->getMnemo()); die(__METHOD__);
        if (isset($config['status']['validation'][$namespace][$mode][$status->getMnemo()])) {
            $statusFields = $config['status']['validation'][$namespace][$mode][$status->getMnemo()];
            if ($statusFields) {
                $route = $current->currentRoute();
                $target = $route->getParam('controller') . '/' . $route->getParam('action');
                if ($target === 'checkout-booking/view') {
                    // Суть роботи прав доступу по полях насупна:
                    // Прописуємо конфіг на основі статусів
                    // Проставляємо загальні права але перед початком встановлення доступу до поля перевіряємо і проставляємо статуси
                    // і вже після цього при необхідності перезаписуємо значеннями з бд.
                    //
                    //
                    //
                    //
                    //
                    //
                    //
                    //$statusFields['checkoutBooking']['cart']['cartItems']['price'] = ['disabled' => true];
                    //$permissionTree = $this->getPermissionTree()['field']['fields'];
                    $simplerPlugin = $this->getSimplerPlugin();
                    $userPlugin = $this->getUserPlugin();
                    $user = $userPlugin->current();
                    $roleMask = '0' . $user->getRoles()->first()->getId() . '0';
                    $fields = $this->getFieldsService()->getAllFieldsByRole($target, $roleMask);
                    $fieldsPermission = $simplerPlugin->setContext($fields)->asAssociate('mnemo');

                    if (isset($fieldsPermission['price'])) {
                        $access = $fieldsPermission['price']['access'];
                        if ($userPlugin->isAdmin() || (Acl::ACCESS_WRITE == $access || Acl::ACCESS_TOTAL == $access)) {
                            $attr = ['price'];
                        } else {
                            $attr = ['price' => ['readonly' => true]];
                        } /*elseif (Acl::ACCESS_WRITE == $fieldsPermission['price']) {

                        }*/
                        $statusFields['checkoutBooking']['cart']['cartItems'] = $attr;
                    }

                }

                $inputFilter = $form->getInputFilter();

                $validationGroup = [];
                $this->applyOptions($statusFields, $form, $inputFilter, $validationGroup);
                if ($validationGroup) {
                    $form->setValidationGroup($validationGroup);
                }
            }
        }
    }

    /**
     * Apply validation group to From
     *
     * @param Form $form
     * @param Status $status Pass this param only for activate MODE_CHANGE
     */
    public function apply($form, $status = null)
    {
        $this->setForm($form);
        $this->setStatus($status);

        $this->applyStatus();
        //$this->applyFields($form);

    }

    protected function applyOptions($validationOptions, $formOrElement, $inputFilter, & $validationGroup = [])
    {
        //\Zend\Debug\Debug::dump($validationGroup);
        //\Zend\Debug\Debug::dump($validationOptions);

        foreach ($validationOptions as $group => $validation) {
            #$targetElement = ($formOrElement instanceof FormCollection)
            #    ? $formOrElement->getTargetElement()
            #    : $formOrElement;

            if ($formOrElement instanceof FormCollection) {
                $targetElement = $formOrElement->getTargetElement();
            } else {
                $targetElement = $formOrElement;
            }

            if (substr($group, 0, 2) === '__') { // prepare optional fields
                $optionalMethod = 'prepareOptional' . ucfirst(substr($group, 2));
                if (method_exists($this, $optionalMethod)) {
                    $this->{$optionalMethod}($validation, $formOrElement);
                }
            } elseif (($targetElement instanceof FieldsetInterface && $targetElement->has($group))
                && (($targetElement = $targetElement->get($group)) instanceof FieldsetInterface)
            ) {
                $validationGroup[$group] = [];
                $this->applyOptions($validation, $targetElement, $inputFilter->get($group), $validationGroup[$group]);
            } else {
                if (is_array($validation)) { // is attributes attached to element in config
                    $validationGroup[] = $group;
                    foreach ($validation as $attr => $value) {
                        $targetElement->setAttribute($attr, $value);

                        if ('required' === $attr) {
                            /** @var \Zend\InputFilter\Input $inputFilter */
                            $inputFilter->get($group)->setRequired($value);
                        }

                        //\Zend\Debug\Debug::dump($attr, $value);
                        //\Zend\Debug\Debug::dump($formOrElement->getAttribute($attr));
                    }
                } elseif (is_string($validation)) {
                    $validationGroup[] = $validation;
                } else {
                    throw new Exception\RuntimeException(sprintf(
                        'Unsupported type "%s" placed in validation groups.', gettype($validation)
                    ));
                }
            }
        }
    }

    protected function prepareOptionalConfig($config, $formOrElement)
    {
        if (isset($config['hydratorStrategy'])) {
            // @link https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md
            $class = 'DoctrineModule\\Stdlib\\Hydrator\\Strategy\\' . $config['hydratorStrategy'];

            //$hydrator = $formOrElement->getHydrator();
            ##$hydrator->addStrategy($formOrElement->getName(), new $class());
            //\Zend\Debug\Debug::dump([$formOrElement->getName(), get_class($hydrator->getStrategy($formOrElement->getName()))]); die(__METHOD__);

            $targetElement = $formOrElement->getTargetElement();
            /** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject $hydrator */
            $hydrator = $targetElement->getHydrator();
            $hydrator->addStrategy($targetElement->getName(), new $class());

            //$targetElement = $this->form->get('invoice')->get('invoiceProducts')->getTargetElement();
            /** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject $hydrator */
            //$hydrator = $targetElement->getHydrator();
            /*\Zend\Debug\Debug::dump([
                $targetElement->getName(),
                get_class($formOrElement),
                //get_class($hydrator->getStrategy('quantityItems')),
                get_class($hydrator),
                get_class($targetElement->getHydrator()),
                get_class($targetElement),
            ]);
            die(__METHOD__);*/


            //'hydrator' => ['addStrategy' => 'DisallowRemoveByValue']
            //\Zend\Debug\Debug::dump([$formOrElement->getName(), get_class($targetElement->getHydrator()), $config]); die(__METHOD__);
        }
    }

    protected function prepareOptionalConditions($config, $formOrElement)
    {
        /** @var \Popov\CheckoutBooking\Form\CheckoutBookingForm $form */

        $validatorManager = $this->getValidatorManager();
        foreach ($config as $validation) {
            /** @var AuthorizedPersonCondition $validator */
            $validator = $validatorManager->get($validation['name'], [
                'status' => $this->getStatus(),
                'item' => $this->getForm()->getObject(),
                'form' => $this->getForm(),
                //'raw_values' => $this->getForm()->getInputFilter()->getRawValues(),
            ]);
            $validator->prepare($formOrElement);
        }
    }

    public function __invoke()
    {
        if (!$args = func_get_args()) {
            return $this;
        }

        return call_user_func([$this, 'apply'], func_get_args());
    }
}
