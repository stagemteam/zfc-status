<?php
/**
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 11.05.16 2:21
 */
namespace Agere\Status\Controller\Plugin;

use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Agere\Module\Controller\Plugin\ModulePlugin;

//use Magere\Status\Service\StatusChanger;
//use Agere\Current\Plugin\Current;

class StatusPlugin extends AbstractPlugin
{
    /** @var */
    protected $fem;

    /** @var ModulePlugin */
    protected $modulePlugin;

    public function setFormElementManager($fem)
    {
        $this->fem = $fem;

        return $this;
    }

    public function getFormElementManager()
    {
        return $this->fem;
    }

    public function setModulePlugin(ModulePlugin $modulePlugin)
    {
        $this->modulePlugin = $modulePlugin;

        return $this;
    }

    public function getModulePlugin()
    {
        if (!$this->modulePlugin) {
            $this->modulePlugin = $this->getController()->plugin('module');
        }
        return $this->modulePlugin;
    }

    public function getFormName($entity)
    {
        //$entityName = is_object($entity) ? get_class($entity) : $entity;
        $entityName = $this->getModulePlugin()->getEntityPlugin()->getRealDoctrineClass($entity);
        $formName = str_replace('Model', 'Form', $entityName) . 'Form';

        return $formName;
    }

    /**
     * @param object|string $entity
     * @return Form $form
     */
    public function getChangeForm($entity)
    {
        $fem = $this->getFormElementManager();
        $formName = $this->getFormName($entity);
        $form = $fem->get($formName);

        return $form;
    }

    /*public function __invoke()
    {
        if (!$args = func_get_args()) {
            return $this;
        }

        return call_user_func([$this, 'apply'], func_get_args());
    }*/
}
