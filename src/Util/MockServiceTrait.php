<?php

/**
 * ITEA Office all rights reserved
 *
 * @category  Admin
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

namespace Testing\Util;

use Laminas\ServiceManager\ServiceManager;

/**
 * Class MockServiceTrait
 *
 * @package Testing\Util
 */
trait MockServiceTrait
{

    /**
     * Store original objects/services here to reset them later
     *
     * @var array
     */
    private $serviceBackup = [];

    protected function mockService(string $service, $mockInstance, ServiceManager $serviceManager): void
    {
        $this->serviceBackup[$service] = $serviceManager->get($service);
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($service, $mockInstance);
        $serviceManager->setAllowOverride(false);
    }

    protected function resetService(string $service, ServiceManager $serviceManager): void
    {
        if (array_key_exists($service, $this->serviceBackup)) {
            $backup = &$this->serviceBackup[$service];
            $serviceManager->setAllowOverride(true);
            $serviceManager->setService($service, $backup);
            $serviceManager->setAllowOverride(false);
            unset($backup);
        }
    }
}
