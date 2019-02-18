<?php
/**
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 11.05.16 2:21
 */
namespace Stagem\ZfcStatus\Controller\Plugin;

use Closure;
use Zend\Stdlib\Exception;
use Zend\EventManager\Event;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Magere\Entity\Controller\Plugin\ModulePlugin;
use Stagem\ZfcStatus\Service\StatusService;
use Stagem\ZfcStatus\Service\StatusChanger;
use Stagem\ZfcStatus\Service\Progress\StatusContext;
use Popov\Progress\Service\ProgressService;

//use Stagem\ZfcStatus\Service\StatusChanger;
//use Popov\Current\Plugin\Current;

class StatusPlugin extends AbstractPlugin
{
    /** @var */
    protected $fem;

    /** @var ModulePlugin */
    protected $modulePlugin;

    /** @var StatusService */
    protected $statusService;

    /** @var ProgressService */
    protected $progressService;

    /** @var StatusChanger */
    protected $statusChanger;

    /** @var Closure */
    protected $contextClosure;

    public function __construct(
        StatusService $statusService,
        ProgressService $progressService,
        StatusChanger $statusChanger,
        Closure $contextClosure
    )
    {
        $this->statusService = $statusService;
        $this->progressService = $progressService;
        $this->statusChanger = $statusChanger;
        $this->contextClosure = $contextClosure;
    }

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

    public function getStatusService()
    {
        return $this->statusService;
    }

    public function getProgressService()
    {
        return $this->progressService;
    }

    public function getStatusChanger()
    {
        return $this->statusChanger;
    }

    /**
     * @return StatusContext
     */
    public function getStatusContext()
    {
        return $this->contextClosure->__invoke();
    }

    /**
     * Hardcode. Save status history
     *
     * @param $item
     * @param $status
     */
    public function writeProgress($item, $status)
    {
        $statusContext = $this->getStatusContext();
        $statusChanger = $this->getStatusChanger();

        $statusChanger->setItem($item);

        $event = new Event();
        $event->setTarget($item);
        $event->setParams([
            'oldStatus' => $statusChanger->getOldStatus(),
            'newStatus' => $status,
        ]);


        $statusContext->setEvent($event);

        $progressService = $this->getProgressService();
        $progressService->writeProgress($statusContext);
    }

    /*public function __invoke()
    {
        if (!$args = func_get_args()) {
            return $this;
        }

        return call_user_func([$this, 'apply'], func_get_args());
    }*/
}
