<?php
/**
 * Validatable plugin factory
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 11.05.16 2:38
 */
namespace Popov\ZfcStatus\Controller\Plugin\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Popov\ZfcStatus\Controller\Plugin\Validatable;
use Magere\Permission\Service\PermissionService;
use Magere\Fields\Service\FieldsService;

class ValidatableFactory
{
    public function __invoke(ServiceLocatorInterface $cpm)
    {
        $sm = $cpm->getServiceLocator();
        $config = $sm->get('Config');
        /** @var PermissionService $permissionService */
        $permissionService = $sm->get('PermissionService');
        /** @var FieldsService $fieldsService */
        $fieldsService = $sm->get('FieldsService');

        $validatable = (new Validatable($config))
            ->setFieldsService($fieldsService)
            /*->setClosureFactory(function($name, $params = []) use ($sm) {
                $object = $sm->get($name);

                return $object;
            })*/
        ;

        //$user = $cpm->get('user')->current();
        //if (!$cpm->get('user')->isAdmin()) {

        //$module = $cpm->get('module')->setContext('Magere\Fields')->getRealModule();
        #$entity = $cpm->get('entity')->setContext('Magere\Fields')->getEntity();

        #$tree = $permissionService->getHumanReadablePermissionTree($entity, $user);
        #if (isset($tree[$entity->getId()])) {
        #    $validatable->setPermissionTree($tree[$entity->getId()]);
        #}

        #if (!$cpm->get('user')->isAdmin()) {
        #    $tree = $permissionService->getHumanReadablePermissionTree($module, $user);
        #    $changer->setPermissionTree($tree[$module->getId()]);
        #}

        return $validatable;

    }
}