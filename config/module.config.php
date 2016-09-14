<?php
namespace Agere\Status;

return array(

	'assetic_configuration' => require_once 'assets.config.php',


	'assets_bundle' => [
		'assets' => [
			'Agere' => [
				//'Agere\Spare\Controller\Spare' => [
				//'spare' => [
				'js' => [
					//'media/js/cart.js',
					__DIR__ . '/../view/public/js/status-button.js',
				],
				//'css' => [
				//	__DIR__ . '/../view/public/css/'
				//]
				//],
			]
			//'media' => ['img', 'fonts']
		]
	],

	'controllers' => [
		'invokables' => [
			'status' => Controller\StatusController::class
		],
	],

	'controller_plugins' => [
		'aliases' => [
			'status' => Controller\Plugin\StatusPlugin::class,
			'statusable' => Controller\Plugin\Statusable::class,
			'validatable' => Controller\Plugin\Validatable::class,
		],
		'factories' => [
			Controller\Plugin\StatusPlugin::class => Controller\Plugin\Factory\StatusPluginFactory::class,
			Controller\Plugin\Statusable::class => Controller\Plugin\Factory\StatusableFactory::class,
			Controller\Plugin\Validatable::class => Controller\Plugin\Factory\ValidatableFactory::class,
		],
	],

	'view_helpers' => [
		'factories' => [
			'status' => View\Helper\Factory\StatusFactory::class,
		],
	],

	'view_manager' => [
		/*'template_map' => [
			'status/partial/buttons' => __DIR__ . '/../view/magere/status/partial/buttons.phtml',
		],*/
		'template_path_stack' => [
			__DIR__ . '/../view',
		],
	],

	'form_elements' => [
		'invokables' => [
			Form\ButtonFieldset::class => Form\ButtonFieldset::class,
		],
		//'factories' => [
		//	'Magere\Status\Form\ButtonFieldset' => Form\Factory\ButtonFieldsetFactory::class,
		//],
	],

	'service_manager' => array(
		'aliases' => [
			'Status' => Model\Status::class,
			'StatusProgress' => Model\Progress::class,
			'StatusService'	=> Service\StatusService::class,
			'StatusProgressService' => Service\ProgressService::class,
			'StatusProgressGrid' => Block\Grid\ProgressGrid::class,
			'StatusGrid' => Block\Grid\StatusGrid::class,

			'StatusChanger' => Service\StatusChanger::class,
			'RuleChecker' => Service\RuleChecker::class,
		],
		'invokables' => [
			Model\Progress::class => Model\Progress::class,
			Model\Status::class => Model\Status::class,
			Service\StatusService::class => Service\StatusService::class,
			//Service\ProgressService::class => Service\ProgressService::class,
		],

		'factories' => [
			Service\ProgressService::class => Service\Factory\ProgressServiceFactory::class,
			'Agere\Status\Service\StatusChanger' => Service\Factory\StatusChangerFactory::class,
			'Agere\Status\Service\RuleChecker' => Service\Factory\RuleCheckerFactory::class,

			/*'Agere\Status\Service\StatusService' => function ($sm) {
				$em = $sm->get('Doctrine\ORM\EntityManager');
				$service = new \Agere\Status\Service\StatusService();
				$service->setServiceManager($sm);

				return $service;
			},*/
		],

		'shared' => [
			Model\Progress::class => false,
			Service\StatusChanger::class => false, // System can have several StatusChanger
		]
	),

	// Doctrine config
	'doctrine' => array(
		'driver' => array(
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Model' => __NAMESPACE__ . '_driver',
				)
			),

			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\YamlDriver',
				'cache' => 'array',
				'extension' => '.dcm.yml',
				'paths' => array(__DIR__ . '/yaml')
			),

		),
	),
);
