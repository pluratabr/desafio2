<?php
/**
 * 
 */
return array(
    'controllers' => array(
        'invokables' => array(
        )
    ),
    'router' => array(
        'routes' => array(
			'axemple-api' => array(
				'type' => 'segment',
				'options' => array(
						'route' => '/exemple-api[/:id]',
						'constraints' => array(
								'id' => '[0-9]+'
						),
						'defaults' => array(
								'controller' => 'Api\Controller\Exemple'
						)
				)
			),
        )
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy'
        )
    )
);
