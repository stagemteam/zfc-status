<?php
/**
 * Status Progress Context
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 06.11.2016 21:56
 */
namespace Popov\ZfcStatus\Service\Progress;

use Zend\Mvc\I18n\Translator;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Popov\Progress\Service\ContextInterface;
use Popov\ZfcStatus\Model\Status;

/**
 * @method Translator getTranslator()
 */
class StatusContext implements ContextInterface
{
    use TranslatorAwareTrait;

    protected $event;

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getItem()
    {
        return $this->event->getTarget();
    }

    public function getExtra()
    {
        return [
            'newStatusId' => $this->getEvent()->getParam('newStatus')->getId(),
            'oldStatusId' => $this->getEvent()->getParam('oldStatus')->getId(),
        ];
    }

    public function getMessage()
    {
        $translator = $this->getTranslator();
        /** @var Status $newStatus */
        $newStatus = $this->getEvent()->getParam('newStatus');
        /** @var Status $oldStatus */
        $oldStatus = $this->getEvent()->getParam('oldStatus');

        $prefix = $translator->translate(
            'Status change',
            $this->getTranslatorTextDomain(),
            $translator->getFallbackLocale()
        ) . ':';

        $template = $translator->translate(
            '%s from %s to %s',
            $this->getTranslatorTextDomain(),
            $translator->getFallbackLocale()
        );

        return sprintf($template, $prefix, $oldStatus->getName(), $newStatus->getName());
    }
}