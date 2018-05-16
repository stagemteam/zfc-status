<?php
/**
 * Created by PhpStorm.
 * User: Vlad Kozak
 * Date: 28.03.16
 * Time: 20:02
 */

namespace Popov\ZfcStatus\Listener;

use DateTime;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Popov\ZfcStatus\Controller\StatusController;
use Zend\EventManager\Event;
use Popov\ZfcStatus\Model\StatusedAtAwareInterface;

class StatusListener //implements ListenerAggregateInterface
{
    //use ListenerAggregateTrait;
    //use ServiceLocatorAwareTrait;

    /*public function attach(EventManagerInterface $events)
    {
        $sharedEventManager = $events->getSharedManager(); // shared events manager

        $this->listeners[] = $sharedEventManager->attach(StatusController::class, 'change.post',
            function(Event $e) {
                $item = $e->getTarget();
                if ($item instanceof StatusedAtAwareInterface) {
                    $item->setStatusedAt(new DateTime('now'));
                }
            }, 110);
    }*/

    public function postChange($e)
    {
        $item = $e->getTarget();
        if ($item instanceof StatusedAtAwareInterface) {
            $item->setStatusedAt(new DateTime('now'));
        }
    }
}