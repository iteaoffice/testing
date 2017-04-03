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

use Admin\Service\AdminService;
use Doctrine\ORM\EntityManager;
use General\Email;
use General\Service\EmailService;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Class AbstractServiceTest
 *
 * @package Testing\Controller
 */
abstract class AbstractServiceTest extends TestCase
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

        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $defaultConfigOverrides = [];

        $configFile = __DIR__ . '/../../../../../config/application.config.php';

        $config = ArrayUtils::merge(
        // Grabbing the full application + module configuration:
            file_exists($configFile) ? include $configFile :
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
     * @return AbstractServiceTest
     */
    protected function setServiceManager(ServiceManager $serviceManager): AbstractServiceTest
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @param string|null $entityClass
     * @param MockObject|null $repositoryMock
     *
     * @return MockObject|EntityManager
     */
    protected function getEntityManagerMock(string $entityClass = null, MockObject $repositoryMock = null): MockObject
    {
        $mockRepository = (isset($entityClass) && isset($repositoryMock));

        $entityManagerMockBuilder = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor();
        $entityManagerMockBuilder->setMethods(['persist', 'flush']);
        if ($mockRepository) { // Just mock the getRepository method
            $entityManagerMockBuilder->setMethods(['persist', 'flush', 'getRepository']);
        }
        $entityManagerMock = $entityManagerMockBuilder->getMock();

        $entityManagerMock->expects($this->any())->method('persist');
        $entityManagerMock->expects($this->any())->method('flush');

        // Mock custom entity repository when provided
        if ($mockRepository) {
            $entityManagerMock->expects($this->atLeastOnce())
                ->method('getRepository')
                ->with($this->equalTo($entityClass))
                ->will($this->returnValue($repositoryMock));
        }

        return $entityManagerMock;
    }

    /**
     * @return MockObject|AdminService
     */
    public function getAdminServiceMock()
    {
        //Mock the admin service
        $adminServiceMock = $this->getMockBuilder(AdminService::class)
            ->setMethods(['flushPermitsByEntityAndId',])->getMock();
        $adminServiceMock->expects($this->any())
            ->method('flushPermitsByEntityAndId')
            ->will($this->returnValue(true));

        return $adminServiceMock;
    }

    /**
     * @return MockObject|EmailService
     */
    public function getEmailServiceMock()
    {
        $email = new Email([], []);

        //Mock the email service
        $emailServiceMock = $this->getMockBuilder(EmailService::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'setTemplate', 'send'])->getMock();
        $emailServiceMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($email));
        $emailServiceMock->expects($this->any())
            ->method('setTemplate')
            ->will($this->returnValue($emailServiceMock));
        $emailServiceMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue('OK'));

        return $emailServiceMock;
    }

}
