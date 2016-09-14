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

use Agere\ZfcDataGrid\Block\AbstractGrid;

class StatusGrid extends AbstractGrid implements ObjectManagerAwareInterface {

    use ProvidesObjectManager;

    protected $createButtonTitle = 'Добавить';
    protected $backButtonTitle = '';

    public function init() {

        $grid = $this->getDataGrid();
        $grid->setId('status');
        $grid->setTitle('Статусы');
        $grid->setRendererName('jqGrid');

        $colId = $this->add([
            'name' => 'Select',
            'construct' => ['id', 'status'],
            'identity' => true,
        ])->getDataGrid()->getColumnByUniqueId('status_id');

        /* $this->add([
             'name' => 'Select',
             'construct' => ['id', 'material'],
             'label' => 'Номер приема',
             'width' => 1,
             'formatters' => [
                 [
                     'name' => 'Link',
                     'link' => ['href' => '/material-category/edit', 'placeholder_column' => $colId] // special config
                 ],
             ],
             'identity' => false,
         ]);*/

        $this->add([
            'name' => 'Select',
            'construct' => ['name', 'status'],
            'label' => 'Статус',
            'translation_enabled' => true,
            'width' => 2,
            'formatters' => [
                [
                    'name' => 'Link',
                    'link' => ['href' => '/status/edit', 'placeholder_column' => $colId] // special config
                ],
            ],
        ]);

        $this->add([
            'name' => 'Select',
            'construct' => ['mnemo', 'status'],
            'label' => 'Мнемо',
            'translation_enabled' => true,
            'width' => 2,
        ]);

        $this->add([
            'name' => 'Select',
            'construct' => ['namespace', 'module'],
            'label' => 'Entity',
            'translation_enabled' => true,
            'width' => 2,
        ]);

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