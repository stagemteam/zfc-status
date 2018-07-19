<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_<package>
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcStatus\Block\Grid;

use Doctrine\ORM\Query\Expr;
use Popov\ZfcDataGrid\Block\AbstractGrid;

class IndexGrid extends AbstractGrid
{
    protected $createButtonTitle = '';

    protected $backButtonTitle = '';

    protected $id = 'status';

    public function init()
    {
        $grid = $this->getDataGrid();
        //$view = $this->getRenderer();
        $grid->setTitle('Statuses');
        //$grid->setRendererName('jqGrid');
        $colId = $this->add([
            'name' => 'Select',
            'construct' => ['id', $this->id],
            'identity' => true,
        ])->getDataGrid()->getColumnByUniqueId($this->id . '_id');
        $this->add([
            'name' => 'Select',
            'construct' => ['name', 'status'],
            'label' => 'name',
            'formatters' => [[
                'name' => 'Link',
                'link' => ['href' => '/admin/status/edit/%s', 'placeholder_column' => 'status_id'],
                'attributes' => ['target' => '_blank'],
            ]],
            'translation_enabled' => true,
            'width' => 2,
        ]);
        $this->add([
            'name' => 'Select',
            'construct' => ['mnemo', 'status'],
            'label' => 'mnemo',
            'translation_enabled' => true,
            'width' => 2,
        ]);
        $this->add([
            'name' => 'Select',
            'construct' => ['hidden', 'status'],
            'label' => 'hidden',
            'translation_enabled' => true,
            'width' => 2,
        ]);
        $this->add([
            'name' => 'Select',
            'construct' => ['automatically', 'status'],
            'label' => 'isOriginal',
            'translation_enabled' => true,
            'width' => 2,
        ]);
        $this->add([
            'name' => 'Select',
            'construct' => ['color', 'status'],
            'label' => 'color',
            'translation_enabled' => true,
            'width' => 2,
        ]);
        $this->add([
            'name' => 'Select',
            'construct' => ['mnemo', 'entity'],
            'label' => 'Mnemo',
            'translation_enabled' => true,
            'width' => 2,
        ]);
        $this->add([
            'name' => 'Select',
            'construct' => ['domain', 'marketplace'],
            'label' => 'Domain',
            'translation_enabled' => true,
            'width' => 2,
        ]);

        return $grid;
    }
}