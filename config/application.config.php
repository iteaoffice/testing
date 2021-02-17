<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

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
