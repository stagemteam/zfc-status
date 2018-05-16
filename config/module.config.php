<?php
namespace Popov\ZfcStatus;

return array(

    'event_manager' => require_once 'listener.config.php',

    'assetic_configuration' => require_once 'assets.config.php',

    'progress' => [
        __NAMESPACE__ => [
            'context' => Service\Progress\StatusContext::class,
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
		'template_map' => [
			'status/progress' => __DIR__ . '/../view/magere/status/progress.phtml',
		],
		'template_path_stack' => [
			__DIR__ . '/../view',
		],
	],

	'form_elements' => [
		'invokables' => [
			Form\ButtonFieldset::class => Form\ButtonFieldset::class,
		],
		//'factories' => [
		//	'Popov\ZfcStatus\Form\ButtonFieldset' => Form\Factory\ButtonFieldsetFactory::class,
		//],
        'shared' => [
            Form\ButtonFieldset::class => false
        ]
	],

	'service_manager' => array(
		'aliases' => [
			'Status' => Model\Status::class,
			//'StatusProgress' => Model\Progress::class,
			'StatusService'	=> Service\StatusService::class,
			//'StatusProgressService' => Service\ProgressService::class,
			'StatusProgressGrid' => Block\Grid\ProgressGrid::class,

			'StatusChanger' => Service\StatusChanger::class,
			'RuleChecker' => Service\RuleChecker::class,
        ],
        'invokables' => [
            //Model\Progress::class => Model\Progress::class,
            //Service\ProgressService::class => Service\ProgressService::class,
            Service\Progress\StatusContext::class => Service\Progress\StatusContext::class,
        ],

		'factories' => [
			//Service\ProgressService::class => Service\Factory\ProgressServiceFactory::class,
			Service\StatusChanger::class => Service\Factory\StatusChangerFactory::class,
			Service\RuleChecker::class => Service\Factory\RuleCheckerFactory::class,
            //Service\Progress\StatusContext::class => Service\Progress\Factory\StatusContextFactory::class,


            /*'Popov\ZfcStatus\Service\StatusService' => function ($sm) {
				$em = $sm->get('Doctrine\ORM\EntityManager');
				$service = \Magere\Popov\Service\Factory\Helper::create('status/status', $em);
				$service->setServiceLocator($sm);

				return $service;
			},*/
		],
        'delegators' => [
            Service\Progress\StatusContext::class => [
                \Stagem\ZfcTranslator\Service\Factory\TranslatorDelegatorFactory::class
            ]
        ],

		'shared' => [
            //Model\Progress::class => false,
			Service\StatusChanger::class => false, // System can have several StatusChanger
		]
	),

    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain' => __NAMESPACE__,
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src//Model'],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Model' => __NAMESPACE__ . '_driver',
                ],
            ],
        ],
    ],

	// @link http://adam.lundrigan.ca/2012/07/quick-and-dirty-zf2-zend-navigation/
	// All navigation-related configuration is collected in the 'navigation' key
	'navigation' => array(
		// The DefaultNavigationFactory we configured in (1) uses 'default' as the sitemap key
		'default' => array(
			// And finally, here is where we define our page hierarchy
			'status' => array(
				'module' => 'status',
				'label' => 'Главная',
				'route' => 'default',
				'controller' => 'index',
				'action' => 'index',
				'pages' => array(
					'settings-index' => array(
						'label'      => 'Настройки',
						'route'      => 'default',
						'controller' => 'settings',
						'action'     => 'index',
						'pages' => array(
							'status-index' => array(
								'label' => 'Статусы',
								'route' => 'default',
								'controller' => 'status',
								'action' => 'index',
								'pages' => array(
									'status-add' => array(
										'label' => 'Добавить',
										'route' => 'default',
										'controller' => 'status',
										'action' => 'add',
									),
									'status-edit' => array(
										'label' => 'Редактировать',
										'route' => 'default/id',
										'controller' => 'status',
										'action' => 'edit',
									),
								),
							),
						),
					),
				),
			),
		),
	),

);
