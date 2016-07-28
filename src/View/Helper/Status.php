<?php
namespace Agere\Status\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Agere\Status\Service\StatusService;
use Agere\Status\Service\ProgressService;

class Status extends AbstractHelper {
	/** @var StatusService */
	protected $statusService;

	/** @var ProgressService */
	protected $progressService;
	protected $_collections;
	protected $_tmpMnemo;
	protected $_tmpHidden;


	/**
	 * @param StatusService $statusService
	 * @param ProgressService $progressService
	 * @param $translator
	 */
	public function __construct(StatusService $statusService, ProgressService $progressService, $translator) {
		$this->statusService = $statusService;
		$this->progressService = $progressService;
		$this->_translator = $translator;
		$this->_translator->setTranslatorTextDomain('Magere\Permission');
	}

	public function progress($item) {
		return $this->progressService->getProgress($item)->getQuery()->getResult();
	}

	/**
	 * @param string $entityMnemo
	 * @param null|string $hidden
	 * @return mixed
	 */
	protected function statusCollections($entityMnemo, $hidden = '') {
		return $this->statusService->getItemsCollection($entityMnemo, $hidden);
	}

	/**
	 * @param int|array $valSelected
	 * @param string $title
	 * @param int $titleVal
	 * @param string $entityMnemo
	 * @param null|string $hidden
	 * @return string
	 */
	public function statusList($valSelected, $title = '', $titleVal = 0, $entityMnemo = '', $hidden = 1) {
		$translate = $this->_translator;
		$strOptions = '<option value="' . $titleVal . '">' . $title . '</option>';
		if ($this->_collections == null || $this->_tmpMnemo != $entityMnemo || $this->_tmpHidden != $hidden) {
			$this->_collections = $this->statusCollections($entityMnemo, $hidden);
		}

        //\Zend\Debug\Debug::dump(count($this->_collections)); die(__METHOD__);

		foreach ($this->_collections as $collection) {


            $selected = ((!is_array($valSelected) && $collection[0]->getId() == $valSelected) || (is_array($valSelected) && in_array($collection[0]->getId(), $valSelected)))
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
	 */
	public function statusArray($entityMnemo = '') {
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