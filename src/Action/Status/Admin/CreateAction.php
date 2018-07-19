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

use Popov\ZfcStatus\Form\StatusForm;
use Popov\ZfcStatus\Service\StatusService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Server\RequestHandlerInterface;
#use Psr\Http\Server\RequestHandlerInterface;
use Zend\View\Model\ViewModel;
use Stagem\ZfcAction\Page\AbstractAction;
use Popov\ZfcUser\Controller\Plugin\UserPlugin;
use Stagem\ZfcPool\Controller\Plugin\PoolPlugin;
use Popov\ZfcForm\FormElementManager;


/**
 * @method UserPlugin user()
 * @method PoolPlugin pool()
 */
class CreateAction extends AbstractAction
{

    /**
     * @var QuestionService
     */
    protected $statusService;

    /**
     * @var FormElementManager
     */
    protected $fm;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function __construct(
        StatusService $statusService,
        FormElementManager $fm
        //UrlHelper $urlHelper
    )
    {
        $this->statusService = $statusService;
        $this->fm = $fm;
        //$this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(ViewModel::class, $this->action($request)));
    }

    public function action(ServerRequestInterface $request)
    {
        /** @var \Stagem\Question\Model\Question $status */
        $status = ($status = $this->statusService->find($id = (int) $request->getAttribute('id')))
            ? $status
            : $this->statusService->getObjectModel();

        /** @var QuestionForm $form */
        $form = $this->fm->get(StatusForm::class);
        $form->bind($status);



        $view = new ViewModel([
            'form' => $form,
            'action' => 'edit'
        ]);


        return $view;
    }
}