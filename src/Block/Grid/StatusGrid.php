<?php
/**
 * Created by PhpStorm.
 * User: ruslana
 * Date: 19.04.16
 * Time: 1:25
 */
namespace Agere\Status\Block\Grid;

use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Action;

use Agere\Grid\Block\AbstractGrid;

class StatusGrid extends AbstractGrid implements ObjectManagerAwareInterface {

    use ProvidesObjectManager;

    protected $createButtonTitle = 'Добавить';
    protected $backButtonTitle = '';

    public function init() {

        /** @var ProductRepository $repository */
        $grid = $this->getDataGrid();
        $route = $this->getRouteMatch();
        $view = $this->getViewRenderer();

        $grid->setId('status');
        $grid->setTitle('Статусы');

        $colId = new Column\Select('id', 'status');
        $colId->setIdentity();
        $grid->addColumn($colId);


        $deleteUrl = $view->url($route->getMatchedRouteName(), [
            'controller' => $route->getParam('controller'),
            'action' => 'delete'
        ]);
        $massAction = new Action\Mass();
        $massAction->setTitle('Удалить');
        $massAction->setLink($deleteUrl);
        $grid->addMassAction($massAction);

        $editUrl = $view->url($route->getMatchedRouteName(), [
            'controller' => $route->getParam('controller'),
            'action' => 'edit'
        ]);
        $formatter = <<<FORMATTER
function (value, options, rowObject) {
	return '<a href="{$editUrl}/' + rowObject.status_id + '" >' + value + '</a>';
}
FORMATTER;

        $col = new Column\Select('name', 'status');
        $col->setLabel('Статус');
        $col->setTranslationEnabled();
        $col->setWidth(2);
        $col->setRendererParameter('formatter', $formatter, 'jqGrid');
        $grid->addColumn($col);

        $col = new Column\Select('mnemo', 'status');
        $col->setLabel('Мнемо');
        $col->setTranslationEnabled();
        $col->setWidth(2);
        $grid->addColumn($col);

        $col = new Column\Select('namespace', 'module');
        $col->setLabel('Entity');
        $col->setTranslationEnabled();
        $col->setWidth(2);
        $grid->addColumn($col);

        return $grid;
    }

    public function initToolbar() {
        $grid = $this->getDataGrid();
        $toolbar = $this->getToolbar();
        $route = $this->getRouteMatch();

        $grid->getResponse()->setVariable('exportRenderers', ['PHPExcel' => 'Excel']);

        return $toolbar;
    }

}