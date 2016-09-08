<?php
/**
 * Status Progress Grid Block
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 25.12.2015 21:31
 */
namespace Agere\Status\Block\Grid;

use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Zend\Stdlib\Exception\RuntimeException;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Column\Type;

use Agere\Barcode\Column\Formatter;
use Agere\Spare\Model\Repository\ProductRepository;
use Agere\Grid\Block\AbstractGrid;

class ProgressGrid extends AbstractGrid implements ObjectManagerAwareInterface {

	use ProvidesObjectManager;

	protected $createButtonTitle = '';
	protected $backButtonTitle = '';

	public function init() {
		/** @var ProductRepository $repository */
		$grid = $this->getDataGrid();
		$route = $this->getRouteMatch();
		$view = $this->getViewRenderer();

		$grid->setId('statusProgress_grid');
		$grid->setTitle('История статусов');
		$grid->setRendererName('jqGrid');

		$colId = new Column\Select('id', 'statusProgress');
		$colId->setIdentity();
		$grid->addColumn($colId);

		$col = new Column\Select('name', 'status');
		$col->setLabel('Статус');
		$col->setTranslationEnabled();
		//$col->setUserSortDisabled(true);
		//$col->setUserFilterDisabled(true);
		//$col->setRowClickDisabled(true);
        $col->setWidth(2);
		$grid->addColumn($col);

		$col = new Column\Select('email', 'user');
		$col->setLabel('Пользователь');
		$col->setTranslationEnabled();
		//$col->addStyle(new Style\Align(Style\Align::$LEFT));
		$col->setWidth(3);
		$grid->addColumn($col);

		$col = new Column\Select('mnemo', 'module');
		$col->setLabel('Модуль');
		$col->setTranslationEnabled();
		$col->setWidth(2);
		$grid->addColumn($col);

		$colType = new Type\DateTime(
			'Y-m-d H:i:s',
			\IntlDateFormatter::MEDIUM,
			\IntlDateFormatter::MEDIUM
		);
		$colType->setSourceTimezone('UTC');
		$colType->setOutputTimezone('Europe/Kiev');
        //$colType = new Type\DateTime();
        $col = new Column\Select('modifiedAt', 'statusProgress');
        $col->setLabel('Дата');
        $col->setTranslationEnabled();
        $col->setType($colType);
        //$col->addStyle(new Style\Align(Style\Align::$LEFT));
        $col->setWidth(2);
        $grid->addColumn($col);

		return $grid;
	}

	public function initToolbar() {
		$grid = $this->getDataGrid();
		$toolbar = $this->getToolbar();
		$route = $this->getRouteMatch();

		//$actionBlock = $toolbar->createActionPanel();
		//$actionBlock = $this->block('block/admin/actionPanel');

		#$toolbar->createActionPanel('Standard')
			#->addAction('Delete', [$route->getMatchedRouteName() => [
			#	'controller' => $route->getParam('controller'),
			#	'action'     => 'delete',
			#]])->addAction('Change status', [$route->getMatchedRouteName() => [
			#	'controller' => $route->getParam('controller'),
			#	'action'     => 'changeStatus',
			#]], ['group' => 'prop', 'position' => 50])
		#; // action: what to do with selected items

		return $toolbar;
	}

}