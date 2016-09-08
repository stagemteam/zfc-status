<?php
/**
 * Plugin which add status buttons to form.
 * This allow add status buttons to different fieldsets in form
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 26.03.16 19:37
 */
namespace Agere\Status\Controller\Plugin;

use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Agere\Entity\Controller\Plugin\Module as ModulePlugin;
use Agere\Status\Service\StatusChanger;
use Agere\Status\Form\ButtonFieldset;

class Statusable extends AbstractPlugin
{
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

    public function injectModule($modulePlugin)
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
        #$itemName = get_class($item);
        #$moduleName = $current->currentModule($itemName);
        //\Zend\Debug\Debug::dump(get_class($url)); die(__METHOD__);
        #$module = $moduleService->getOneItem($moduleName, 'namespace');
        //$module = $modulePlugin->setRealContext($item)->getModule();

        $module = $modulePlugin->setContext($item)->getModule();
        $status = $changer->setModule($module)->setItem($item)->getOldStatus();

        return $status;
    }

    /**
     * Apply status buttons to form
     *
     * @param Form|Fieldset $form
     * @param $item
     */
    public function apply($form, $item, $patient)
    {
        foreach ($form as $element) {
            if ($element instanceof Fieldset) {
                $fieldset = $element;
                if ($fieldset instanceof ButtonFieldset) {
                    $this->attachButtons($fieldset, $item, $patient);
                } else {
                    $method = 'get' . ucfirst($fieldset->getName());
                    if (method_exists($item, $method)) { // @todo: Реалізувати перевірку на основі related form InvoiceProductGrid::prepareColumns()
                        $this->apply($fieldset, $item->{$method}(), $patient);
                    }
                }
            }
        }
    }

    protected function attachButtons($fieldset, $item, $patient)
    {
        $url = $this->getUrl();
        $changer = $this->getStatusChanger();
        $status = $this->getStatus($item);
        $itemClass = get_class($item);

        foreach ($status->getWorkflow() as $workflow) {
            if ($changer->canChangeTo($workflow)/* && $changer->checkRule($workflow->getRule())*/) {
                $fieldset->add([
                    'name' => 'status-' . $workflow->getMnemo(),
                    'type' => 'button',
                    'options' => [
                        'label' => $workflow->getName(),
                    ],
                    'attributes' => [
                        'value' => $workflow->getName(),
                        'class' => 'btn btn-primary btn-xs btn-changeStatus',
                        //'data-status' => $workflow->getMnemo(),
                        'data-status' => json_encode([
                            'status' => $workflow->getMnemo(),
                            'item' => $itemClass,
                            'itemId' => $item->getId(),
                            'patient' => $patient->getId()
                        ]),
                        'data-action' => $url->fromRoute('default', [
                            'controller' => 'status',
                            'action' => 'change',
                        ]),
                    ],
                ]);
            }
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