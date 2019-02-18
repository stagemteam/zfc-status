<?php
/**
 * Plugin which add status buttons to form.
 * This allow add status buttons to different fieldsets in form
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 26.03.16 19:37
 */
namespace Stagem\ZfcStatus\Controller\Plugin;

use Stagem\ZfcStatus\Model\Status;
use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Magere\Entity\Controller\Plugin\ModulePlugin as ModulePlugin;
use Stagem\ZfcStatus\Service\StatusChanger;
use Stagem\ZfcStatus\Form\ButtonFieldset;

class Statusable extends AbstractPlugin
{
    use TranslatorAwareTrait;

    /** @var Url */
    protected $url;

    /** @var [] */
    protected $config;

    /** @var ModulePlugin */
    protected $modulePlugin;

    /** @var StatusChanger */
    protected $statusChanger;

    public function __construct(StatusChanger $statusChanger)
    {
        $this->statusChanger = $statusChanger;
    }

    public function injectUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function injectConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function injectModulePlugin($modulePlugin)
    {
        $this->modulePlugin = $modulePlugin;

        return $this;
    }

    /**
     * @return ModulePlugin
     */
    public function getModulePlugin()
    {
        return $this->modulePlugin;
    }

    public function getCurrentPlugin()
    {
        return $this->getModulePlugin()->getCurrentPlugin();
    }

    public function getStatusChanger()
    {
        return $this->statusChanger;
    }

    protected function getSm()
    {
        return $this->getController()->getServiceLocator();
    }

    public function getStatus($item)
    {
        $changer = $this->getStatusChanger();
        $modulePlugin = $this->getModulePlugin();
        $entityPlugin = $modulePlugin->getEntityPlugin();
        #$itemName = get_class($item);
        #$moduleName = $current->currentModule($itemName);
        //\Zend\Debug\Debug::dump(get_class($url)); die(__METHOD__);
        #$module = $moduleService->getOneItem($moduleName, 'namespace');
        //$module = $modulePlugin->setRealContext($item)->getModule();

        $entity = $entityPlugin->setContext($item)->getEntity();
        $status = $changer->setEntity($entity)->setItem($item)->getOldStatus();

        //\Zend\Debug\Debug::dump(get_class($item));
        //\Zend\Debug\Debug::dump($module->getId());
        //\Zend\Debug\Debug::dump($module->getMnemo());
        //\Zend\Debug\Debug::dump(get_class($item));

        return $status;
    }

    public function hasStatus($item)
    {
        $changer = $this->getStatusChanger();

        return $changer->hasItemWithStatus($item);
    }

    /**
     * Apply status buttons to form
     *
     * @param Form|Fieldset $form
     * @param $item
     */
    //public function apply($form, $item) // видалив $item так як це зайвий параметр, достатньо передавати форму з уже підв'язаними (bind) об'єктом
    public function apply($form)
    {
        foreach ($form as $element) {
            //\Zend\Debug\Debug::dump(get_class($element)); //die(__METHOD__);
            if ($element instanceof Fieldset) {
            //if ($element) {
                $fieldset = $element;
                if ($fieldset instanceof ButtonFieldset) {
                    //\Zend\Debug\Debug::dump([get_class($form), get_class($form->getObject()), __METHOD__]); //die(__METHOD__);
                    $this->attachButtons($fieldset, $form->getObject());
                } elseif ($form->getName() === $fieldset->getName()) {
                    $this->apply($fieldset);
                } else {
                    $method = 'get' . ucfirst($fieldset->getName());
                    //\Zend\Debug\Debug::dump($method);
                    //\Zend\Debug\Debug::dump(method_exists($item, $method));
                    //if (method_exists($item, $method)) { // @todo: Реалізувати перевірку на основі related form InvoiceProductGrid::prepareColumns()
                    if (method_exists($item = $form->getObject(), $method)) {
                        //$this->apply($fieldset, (($item->{$method}()) ?: $fieldset->getObject()));
                        $this->apply($fieldset);
                    }
                }
            }
        }
    }

    protected function attachButtons($fieldset, $item)
    {
        //$modulePlugin = $this->getModulePlugin();
        //$entityPlugin = $modulePlugin->getEntityPlugin();
        //$url = $this->getUrl();
        $changer = $this->getStatusChanger()->reset();
        $status = $this->getStatus($item);
        //$itemClass = $entityPlugin->getRealDoctrineClass($item);

        foreach ($status->getWorkflow() as $workflow) {
            //\Zend\Debug\Debug::dump(($workflow->getMnemo()));
            if ($changer->canChangeTo($workflow)/* && $changer->checkRule($workflow->getRule())*/) {
                $fieldset->add($this->getButtonConfig($workflow, $item));
                /*$fieldset->add([
                    'name' => 'status-' . $workflow->getMnemo(),
                    'type' => 'button',
                    'options' => [
                        'label' => $this->__('button:' . $workflow->getMnemo(), $workflow->getName(), $itemClass),
                    ],
                    'attributes' => [
                        'value' => 'button:' . $workflow->getMnemo(),
                        'class' => 'btn btn-primary btn-changeStatus',
                        //'data-status' => $workflow->getMnemo(),
                        'data-status' => json_encode([
                            'status' => $workflow->getMnemo(),
                            'item' => $itemClass,
                            'itemId' => $item->getId(),
                        ]),
                        'data-action' => $url->fromRoute('default', [
                            'controller' => 'status',
                            'action' => 'change',
                        ]),
                    ],
                ]);*/
            }
        }
    }

    public function getButtonConfig(Status $status, $item): array
    {
        static $classes = [];

        $url = $this->getUrl();
        $modulePlugin = $this->getModulePlugin();
        $entityPlugin = $modulePlugin->getEntityPlugin();
        $itemClass = isset($classes[$class = get_class($item)])
            ? $classes[$class]
            : $classes[$class] = $entityPlugin->getRealDoctrineClass($item);

        $attributes = [
            'name' => 'status-' . $status->getMnemo(),
            'type' => 'button',
            'options' => [
                'label' => $this->__('button:' . $status->getMnemo(), $status->getName(), $itemClass),
            ],
            'attributes' => [
                'value' => 'button:' . $status->getMnemo(),
                'class' => 'btn btn-primary btn-changeStatus',
                //'data-status' => $workflow->getMnemo(),
                'data-status' => json_encode([
                    'status' => $status->getMnemo(),
                    'item' => $itemClass,
                    'itemId' => $item->getId(),
                ]),
                'data-action' => $url->fromRoute('default', [
                    'controller' => 'status',
                    'action' => 'change',
                ]),
            ],
        ];

        return $attributes;
    }

    public function __($message, $alternative, $context)
    {
        /*if ($context instanceof TranslatorAwareInterface
            && $context->hasTranslator()
            && $context->isTranslatorEnabled()
        ) {
            die(__METHOD__);
            $translator = $context->getTranslator();
            return $translator->translate($message);
        }*/
        $currentPlugin = $this->getCurrentPlugin();
        $translator = $this->getTranslator();
        //\Zend\Debug\Debug::dump($currentPlugin->currentModule($context));//die(__METHOD__);
        return $translator->translate($message, $currentPlugin->currentModule($context));

        //return $alternative;
    }


    public function __invoke()
    {
        if (!$args = func_get_args()) {
            return $this;
        }

        return call_user_func([$this, 'apply'], func_get_args());
    }
}