<?php
/**
 *
 */
return [
    'modules'                 => [
        'Zend\Router',
        'Zend\Form',
        'Zend\Navigation',
        'Zend\Mvc\Console',
        'Zend\Mvc\Plugin\FlashMessenger'
    ],
    'module_listener_options' => [
        'config_glob_paths'        => [
            'config/autoload/{,*.}{global,local}.php',
        ],
        'config_cache_enabled'     => false,
        'module_map_cache_enabled' => false,
        'module_paths'             => [
            './module',
            './vendor',
        ],
    ],
    'service_manager'         => [
        'use_defaults' => true,
        'factories'    => [],
    ],
    'zfctwig'                 => [
        'environment_options' => [
            'cache' => false,
        ],
    ],
];