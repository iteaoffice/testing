<?php

/**
 *
 */

return [
    'modules'                 => [
        'Laminas\Router',
        'Laminas\Form',
        'Laminas\Navigation',
        'Laminas\Mvc\Console',
        'Laminas\Mvc\Plugin\FlashMessenger'
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
