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

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Class AbstractServiceTest
 *
 * @package Testing\Controller
 */
abstract class AbstractInputFilterTest extends TestCase
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
    public function setUp(): void
    {
        if (!defined('ITEAOFFICE_ENVIRONMENT')) {
            define('ITEAOFFICE_ENVIRONMENT', 'test');
        }

        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $defaultConfigOverrides = [];

        $configFile = __DIR__ . '/../../../../../config/application.config.php';

        $config = ArrayUtils::merge(
            // Grabbing the full application + module configuration:
            file_exists($configFile)
                ? include $configFile
                :
                include __DIR__ . '/../../config/application.config.php',
            $defaultConfigOverrides,
            $this->configOverrides
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
     * @return AbstractInputFilterTest
     */
    protected function setServiceManager(ServiceManager $serviceManager): AbstractInputFilterTest
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @param string|null     $entityClass
     * @param MockObject|null $repositoryMock
     *
     * @return MockObject|EntityManager
     */
    protected function getEntityManagerMock(string $entityClass = null, MockObject $repositoryMock = null)
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
                ->willReturn($repositoryMock);
        }

        return $entityManagerMock;
    }
}
