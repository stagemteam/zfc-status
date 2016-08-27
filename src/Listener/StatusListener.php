<?php
/**
 * Created by PhpStorm.
 * User: Vlad Kozak
 * Date: 28.03.16
 * Time: 20:02
 */

namespace Agere\Status\Listener;

use Zend\ServiceManager\ServiceLocatorAwareTrait;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

use Agere\Status\Controller\StatusController;
use Agere\Status\Service\ProgressService;

class StatusListener implements ListenerAggregateInterface {

    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;

    public function attach(EventManagerInterface $events) {
        $sm = $this->getServiceLocator();
        $sem = $events->getSharedManager(); // shared events manager

        $this->listeners[] = $sem->attach(StatusController::class, 'change.post', function($e) use($sm) {
            $item = $e->getTarget();
            $newStatus = $e->getParam('newStatus');
            //$oldStatus = $e->getParam('oldStatus');
            /** @var ProgressService $progressService */
            $progressService = $sm->get('StatusProgressService');
            $progressService->writeProgress($item, $newStatus);

        }, 100);
    }
}