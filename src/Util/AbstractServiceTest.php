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
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
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
     * @return MockObject|AdminService
     */
    public function getAdminServiceMock()
    {
        //Mock the admin service
        $adminServiceMock = $this->getMockBuilder(AdminService::class)->disableOriginalConstructor()
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
        //Mock the email service
        $emailServiceMock = $this->getMockBuilder(EmailService::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWebInfo', 'send','setSender','addTo'])->getMock();
        $emailServiceMock->expects($this->any())->method('setWebInfo');
        $emailServiceMock->expects($this->any())->method('setSender');
        $emailServiceMock->expects($this->any())->method('addTo');
        $emailServiceMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue(true));

        return $emailServiceMock;
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
     * @param string|null     $entityClass
     * @param MockObject|null $repositoryMock
     *
     * @return MockObject|EntityManager
     */
    protected function getEntityManagerMock(string $entityClass = null, $repositoryMock = null)
    {
        $mockRepository = (isset($entityClass) && isset($repositoryMock));

        $entityManagerMockBuilder = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor();
        $mockMethods = ['persist', 'flush', 'remove', 'getClassMetadata'];
        $entityManagerMockBuilder->setMethods($mockMethods);
        if ($mockRepository) { // Mock the getRepository method
            $entityManagerMockBuilder->setMethods(array_merge($mockMethods, ['getRepository']));
        }
        $entityManagerMock = $entityManagerMockBuilder->getMock();

        $entityManagerMock->expects($this->any())->method('persist');
        $entityManagerMock->expects($this->any())->method('flush');
        $entityManagerMock->expects($this->any())->method('remove');

        $metaData = new TestObjectMetadata();
        $entityManagerMock->expects($this->any())->method('getClassMetadata')
            ->will($this->returnValue($metaData));

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

/**
 * Class TestObjectMetadata
 *
 * @package Testing\Util
 */
class TestObjectMetadata implements ClassMetadata
{
    public function getAssociationMappedByTargetField($assocName)
    {
        $assoc = ['children' => 'parent'];
        return $assoc[$assocName];
    }

    public function getAssociationNames()
    {
        return ['parent', 'children'];
    }

    public function getAssociationTargetClass($assocName)
    {
        return __NAMESPACE__ . '\TestObject';
    }

    public function getFieldNames()
    {
        return ['id', 'name'];
    }

    public function getIdentifier()
    {
        return ['id'];
    }

    public function getReflectionClass()
    {
        return new \ReflectionClass($this->getName());
    }

    public function getName()
    {
        return __NAMESPACE__ . '\TestObject';
    }

    public function getTypeOfField($fieldName)
    {
        $types = ['id' => 'integer', 'name' => 'string'];
        return $types[$fieldName];
    }

    public function hasAssociation($fieldName)
    {
        return in_array($fieldName, ['parent', 'children']);
    }

    public function hasField($fieldName)
    {
        return in_array($fieldName, ['id', 'dateCreated', 'dateUpdated']);
    }

    public function isAssociationInverseSide($assocName)
    {
        return ($assocName === 'children');
    }

    public function isCollectionValuedAssociation($fieldName)
    {
        return ($fieldName === 'children');
    }

    public function isIdentifier($fieldName)
    {
        return $fieldName === 'id';
    }

    public function isSingleValuedAssociation($fieldName)
    {
        return $fieldName === 'parent';
    }

    public function getIdentifierValues($entity)
    {
    }

    public function getIdentifierFieldNames()
    {
    }

    public function initializeReflection(ReflectionService $reflService)
    {
    }

    public function wakeupReflection(ReflectionService $reflService)
    {
    }
}
