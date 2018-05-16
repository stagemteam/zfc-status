<?php
namespace Popov\ZfcStatus\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Magere\Entity\View\Helper\ModuleHelper;
use Popov\ZfcStatus\Service\StatusService;
use Popov\Progress\Service\ProgressService;
use Popov\ZfcStatus\Service\StatusChanger;
use Popov\ZfcStatus\Model\Status;

class StatusHelper extends AbstractHelper
{
    /** @var StatusService */
    protected $statusService;

    /** @var ProgressService */
    protected $progressService;

    /** @var StatusChanger */
    protected $statusChanger;

    /** @var ModuleHelper */
    protected $moduleHelper;

    protected $_collections;

    protected $_tmpMnemo;

    protected $_tmpHidden;

    /**
     * @param StatusService $statusService
     * @param ProgressService $progressService
     * @param StatusChanger $statusChanger
     * @param $translator
     */
    public function __construct(
        StatusService $statusService,
        ProgressService $progressService,
        StatusChanger $statusChanger,
        $translator
    )
    {
        $this->statusService = $statusService;
        $this->progressService = $progressService;
        $this->statusChanger = $statusChanger;
        $this->_translator = $translator;
        $this->_translator->setTranslatorTextDomain('Magere\Permission');
    }

    public function getStatusService(): StatusService
    {
        return $this->statusService;
    }

    public function getModuleHelper()
    {
        if (!$this->moduleHelper) {
            $this->moduleHelper = $this->getView()->plugin('module');
        }
        return $this->moduleHelper;
    }

    public function progress($item)
    {
        $moduleHelper = $this->getModuleHelper();
        $module = $moduleHelper->setContext($this)->getRealModule();
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

        //$this->getStatusService()->getEntityManager()->clear();
        $identity = $this->getStatusService()->getEntityManager()->getUnitOfWork()->getIdentityMap();
        $repository = $this->getStatusService()->getRepository();
        $status = $repository->findOneBy([$field => $value]);

        return $status;
    }

    /**
     * @param string $entityMnemo
     * @param null|string $hidden
     * @return mixed
     */
    protected function statusCollections($entityMnemo, $hidden = '')
    {
        return $this->statusService->getItemsCollection($entityMnemo, $hidden);
    }

    /**
     * @param int|array $valSelected
     * @param string $title
     * @param int $titleVal
     * @param string $entityMnemo
     * @param null|string $hidden
     * @return string
     * @deprecated
     */
    public function statusList($valSelected, $title = '', $titleVal = 0, $entityMnemo = '', $hidden = 1)
    {
        $translate = $this->_translator;
        $strOptions = '<option value="' . $titleVal . '">' . $title . '</option>';
        if ($this->_collections == null || $this->_tmpMnemo != $entityMnemo || $this->_tmpHidden != $hidden) {
            $this->_collections = $this->statusCollections($entityMnemo, $hidden);
        }
        //\Zend\Debug\Debug::dump(count($this->_collections)); die(__METHOD__);
        foreach ($this->_collections as $collection) {
            $selected = ((!is_array($valSelected) && $collection[0]->getId() == $valSelected)
                || (is_array($valSelected)
                    && in_array($collection[0]->getId(), $valSelected)))
                ? ' selected="selected"'
                : '';
            $mnemoOption = ($entityMnemo == '') ? ' (' . $translate($collection['mnemo']) . ')' : '';
            $strOptions .= '<option value="' . $collection[0]->getId() . '"' . $selected . '>'
                . $collection[0]->getName() . $mnemoOption
                . '</option>';
        }

        return $strOptions;
    }

    /**
     * @param string $entityMnemo
     * @return array
     * @deprecated
     */
    public function statusArray($entityMnemo = '')
    {
        $options = [];
        if ($this->_collections == null) {
            $this->_collections = $this->statusCollections($entityMnemo);
        }
        foreach ($this->_collections as $collection) {
            $options[$collection[0]->getId()] = $collection[0]->getName();
        }

        return $options;
    }
}