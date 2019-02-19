<?php
namespace Stagem\ZfcStatus\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\I18n\Translator\TranslatorInterface;
use Popov\ZfcEntity\Helper\EntityHelper;
use Popov\ZfcEntity\Helper\ModuleHelper;
use Stagem\ZfcStatus\Service\StatusService;
use Stagem\ZfcStatus\Service\StatusChanger;
use Stagem\ZfcStatus\Model\Status;
use Stagem\ZfcProgress\Service\ProgressService;

class StatusHelper extends AbstractHelper
{
    /** @var StatusService */
    protected $statusService;

    /** @var ProgressService */
    protected $progressService;

    /** @var StatusChanger */
    protected $statusChanger;

    /** @var EntityHelper */
    protected $entityHelper;

    /** @var ModuleHelper */
    protected $moduleHelper;

    /**
     * @param StatusService $statusService
     * @param ProgressService $progressService
     * @param StatusChanger $statusChanger
     * @param ModuleHelper $moduleHelper
     * @param $translator
     */
    public function __construct(
        StatusService $statusService,
        ProgressService $progressService,
        StatusChanger $statusChanger,
        ModuleHelper $moduleHelper,
        TranslatorInterface $translator = null
    )
    {
        $this->statusService = $statusService;
        $this->progressService = $progressService;
        $this->statusChanger = $statusChanger;
        $this->moduleHelper = $moduleHelper;
        //$this->_translator = $translator;
        //$this->_translator->setTranslatorTextDomain('Magere\Permission');
    }

    public function getStatusService(): StatusService
    {
        return $this->statusService;
    }

    public function getModuleHelper(): ModuleHelper
    {
        return $this->moduleHelper;
    }

    public function progress($item)
    {
        $moduleHelper = $this->getModuleHelper();
        $module = $moduleHelper->setContext($this)->getModule();
        return $this->progressService->getProgressByContext($item, $module)->getQuery()->getResult();
    }

    /**
     * Get current status of the item
     * If status not set yet then return default status
     *
     * @param $item
     * @return Status
     */
    public function current($item)
    {
        if ($status = $item->getStatus()) {
            return $status;
        }

        return $this->statusChanger->setItem($item)->getDefaultStatus();
    }

    public function getBy($value, $field = 'id')
    {
        $repository = $this->getStatusService()->getRepository();
        $status = $repository->findOneBy([$field => $value]);

        return $status;
    }


    /**
     * @param object|string $entity
     * @return Form $form
     */
    /*public function getChangeForm($entity)
    {
        $fem = $this->getFormElementManager();
        $formName = $this->getFormName($entity);
        $form = $fem->get($formName);

        return $form;
    }*/

}