<?php
/**
 * Progress Service Factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 03.11.16 19:01
 */
namespace Popov\ZfcStatus\Service\Progress\Factory;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\PluginManager;
use Popov\ZfcDataGrid\Service\Progress\DataGridContext;
use Popov\ZfcStatus\Service\Progress\StatusContext;
use Magere\Entity\Controller\Plugin\ModulePlugin;
use Magere\Fields\Service\FieldsService;
use Popov\Simpler\Plugin\SimplerPlugin;

class StatusContextFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var PluginManager $cpm */
        //$cpm = $container->get('ControllerPluginManager');
        /** @var FieldsService $fieldsService */
        //$fieldsService = $container->get('FieldsService');
        /** @var ModulePlugin $modulePlugin */
        //$modulePlugin = $cpm->get('module');
        /** @var SimplerPlugin $simplerPlugin */
        //$simplerPlugin = $cpm->get('simpler');

        return (new StatusContext(/*$modulePlugin, $simplerPlugin, $fieldsService*/));
    }
}