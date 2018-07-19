<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Stagem Team
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Stagem
 * @package Stagem_Patient
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcStatus\Action\Status\Admin;

use Popov\ZfcStatus\Block\Grid\IndexGrid;
use Popov\ZfcStatus\Service\StatusService;

use Stagem\Product\Block\Grid\RankGrid;
use Stagem\Product\Service\RankService;
//use Stagem\Product\Service\ContentService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Server\RequestHandlerInterface;
#use Psr\Http\Server\RequestHandlerInterface;
use Zend\View\Model\ViewModel;
use Stagem\ZfcAction\Page\AbstractAction;
use Popov\ZfcUser\Controller\Plugin\UserPlugin;
use Stagem\ZfcPool\Controller\Plugin\PoolPlugin;
/**
 * @method UserPlugin user()
 * @method PoolPlugin pool()
 */
class IndexAction extends AbstractAction
{
    /**
     * @var rankService
     */
    protected $statusService;

    /**
     * @var rankGrid
     */
    protected $statusGrid;

    public function __construct(StatusService $statusService, IndexGrid $statusGrid)
    {
        $this->statusService = $statusService;
        $this->statusGrid = $statusGrid;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(ViewModel::class, $this->action($request)));
    }

    /**
     * Execute the request
     *
     * @param ServerRequestInterface $request
     * @return ViewModel
     */
    public function action(ServerRequestInterface $request)
    {

        $statuses = $this->statusService->getStatuses();

        $grid = $this->statusGrid->init();

        $grid->setDataSource($statuses);
        $grid->render();

        $dataGridVm = $grid->getResponse();

        return $dataGridVm;
    }
}