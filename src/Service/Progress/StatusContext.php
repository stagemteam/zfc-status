<?php
/**
 * Status Progress Context
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 06.11.2016 21:56
 */
namespace Stagem\ZfcStatus\Service\Progress;

use Popov\ZfcUser\Service\UserAwareTrait;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Stagem\ZfcProgress\Service\ContextInterface;
use Stagem\ZfcStatus\Model\Status;

/**
 * @method TranslatorInterface getTranslator()
 */
class StatusContext implements ContextInterface
{
    use UserAwareTrait;

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

    public function getDescription()
    {
        return '';
    }
}