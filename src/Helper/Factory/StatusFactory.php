<?php
/**
 * Status helper factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 04.02.15 10:30
 */

namespace Stagem\ZfcStatus\View\Helper\Factory;

use Popov\ZfcEntity\Helper\ModuleHelper;
use Psr\Container\ContainerInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Stagem\ZfcStatus\Helper\StatusHelper;

class StatusFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $statusService = $container->get('StatusService');
        $progressService = $container->get('ProgressService');
        $statusChanger = $container->get('StatusChanger');
        $moduleHelper = $container->get(ModuleHelper::class);
        $translator = $container->get(TranslatorInterface::class);

        return new StatusHelper($statusService, $progressService, $statusChanger, $moduleHelper, $translator);
    }
}