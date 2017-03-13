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

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Class AbstractServiceTest
 *
 * @package Testing\Controller
 */
abstract class AbstractFormTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Include service mocking utils
     */
    use MockServiceTrait;

    /**
     * Include dummy contact generation
     */
    use GenerateContactTrait;

    /**
     * Override default config
     */
    use ConfigOverridesTrait;

    /**
     * General test setup
     */
    public function setUp()
    {
        if (!defined('ITEAOFFICE_ENVIRONMENT')) {
            define('ITEAOFFICE_ENVIRONMENT', 'test');
        }

        $defaultConfigOverrides = [
            'module_listener_options' => [
                'config_cache_enabled' => false,
            ],
        ];

        $config = ArrayUtils::merge(
        // Grabbing the full application + module configuration:
            include __DIR__ . '/../../../../../config/application.config.php',
            $defaultConfigOverrides,
            $this->getConfigOverrides()
        );

        // Prepare the service manager
        $serviceManagerConfigArray = isset($config['service_manager']) ? $config['service_manager'] : [];
        $serviceManagerConfig = new ServiceManagerConfig($serviceManagerConfigArray);

        $serviceManager = new ServiceManager();
        $serviceManagerConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $config);

        // Load modules
        $serviceManager->get('ModuleManager')->loadModules();

        $this->setServiceManager($serviceManager);
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }

    /**
     * @param ServiceManager $serviceManager
     *
     * @return AbstractFormTest
     */
    protected function setServiceManager(ServiceManager $serviceManager): AbstractFormTest
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @param string|null $entityClass
     * @param MockObject|null $repositoryMock
     *
     * @return MockObject
     */
    protected function getEntityManagerMock(string $entityClass = null, MockObject $repositoryMock = null): MockObject
    {
        $mockRepository = (isset($entityClass) && isset($repositoryMock));

        $entityManagerMockBuilder = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor();
        if ($mockRepository) { // Just mock the getRepository method
            $entityManagerMockBuilder->setMethods(['getRepository']);
        }
        $entityManagerMock = $entityManagerMockBuilder->getMock();

        // Mock custom entity repository when provided
        if ($mockRepository) {
            $entityManagerMock->expects($this->atLeastOnce())
                ->method('getRepository')
                ->with($this->equalTo($entityClass))
                ->will($this->returnValue($repositoryMock));
        }

        return $entityManagerMock;
    }

}
