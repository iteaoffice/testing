<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Admin
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Testing\Util;

use Zend\ServiceManager\ServiceManager;

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

    /**
     * Mock a service in the service manager, keeping a backup of the original instance
     *
     * @param string         $service
     * @param object         $mockInstance
     * @param ServiceManager $serviceManager
     */
    protected function mockService(string $service, $mockInstance, ServiceManager $serviceManager)
    {
        $this->serviceBackup[$service] = $serviceManager->get($service);
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($service, $mockInstance);
        $serviceManager->setAllowOverride(false);
    }

    /**
     * Reset a mocked object in the service manager to its original instance
     *
     * @param string         $service
     * @param ServiceManager $serviceManager
     */
    protected function resetService(string $service, ServiceManager $serviceManager)
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
