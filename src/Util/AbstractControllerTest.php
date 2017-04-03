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

use Admin\Entity\Access;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use BjyAuthorize\Service\Authorize as BjyAuthorize;
use Contact\Entity\Contact;
use Contact\Provider\Identity\AuthenticationIdentityProvider;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\View\Model\ViewModel;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * Class AbstractControllerTest
 *
 * @package Testing\Controller
 */
abstract class AbstractControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var bool
     */
    protected $traceError = false;

    /**
     * Include service mocking utils
     */
    use MockServiceTrait {
        mockService as parentMockService;
        resetService as parentResetService;
    }

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
        $defaultConfigOverrides = [
            'modules'         => [
                'Zend\Router',
            ],
            'service_manager' => [
                'use_defaults' => true,
                'factories'    => [],
            ],
            'zfctwig'         => [
                'environment_options' => [
                    'cache' => false,
                ],
            ],
        ];

        $configFile = __DIR__ . '/../../../../../config/application.config.php';

        $config = ArrayUtils::merge(
        // Grabbing the full application + module configuration:
            file_exists($configFile) ? include $configFile : [],
            $defaultConfigOverrides,
            $this->getConfigOverrides()
        );

        parent::setUp();
    }

    /**
     * Assert route access for controller
     *
     * @param string $route
     * @param array $accessRoles
     * @param int $expectedStatusCode
     */
    public function assertRouteAccess(string $route, array $accessRoles = [], $expectedStatusCode = 200)
    {
        $this->mockAccessRoles($accessRoles);
        $this->dispatch($route);
        $this->assertResponseStatusCode($expectedStatusCode);
        $this->resetAccessRoles();
    }

    /**
     * Mock access roles for the BjyAuthorize route guard
     *
     * @param array $accessRoles
     */
    protected function mockAccessRoles(array $accessRoles = [])
    {
        // Mock route roles for BjyAuthorize
        $routeAuthMock = $this->getMockBuilder(AuthenticationIdentityProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdentityRoles'])
            ->getMock();

        $routeAuthMock->expects($this->any())
            ->method('getIdentityRoles')
            ->will($this->returnValue(array_map('strtolower', $accessRoles)));

        $serviceManager = $this->getApplicationServiceLocator();
        // Note: yes, this has to be ProviderInterface::class, see BjyAuthorize::load()
        $this->mockService(ProviderInterface::class, $routeAuthMock);
        // BjyAuthorize needs to be re-built with the mocked AuthenticationIdentityProvider
        $this->mockService(BjyAuthorize::class, $serviceManager->build(BjyAuthorize::class));
    }

    /**
     * Mock a service in the service manager, keeping a backup of the original instance
     *
     * @param string $service
     * @param object $mockInstance
     * @param ServiceManager $serviceManager
     */
    protected function mockService(string $service, $mockInstance, ServiceManager $serviceManager = null)
    {
        if (is_null($serviceManager)) {
            $serviceManager = $this->getApplicationServiceLocator();
        }
        $this->parentMockService($service, $mockInstance, $serviceManager);
    }

    /**
     * Reset BjyAuthorize service to its original state
     */
    protected function resetAccessRoles()
    {
        $this->resetService(ProviderInterface::class);
        $this->resetService(BjyAuthorize::class);
    }

    /**
     * Reset a mocked object in the service manager to its original instance
     *
     * @param string $service
     * @param ServiceManager $serviceManager
     */
    protected function resetService(string $service, ServiceManager $serviceManager = null)
    {
        if (is_null($serviceManager)) {
            $serviceManager = $this->getApplicationServiceLocator();
        }
        $this->parentResetService($service, $serviceManager);
    }

    /**
     * Get the return value from the called controller action (typically a ViewModel object)
     *
     * @return ViewModel|mixed
     */
    protected function getReturnValue()
    {
        return $this->getApplication()->getMvcEvent()->getResult();
    }

    /**
     * Mock an identity
     *
     * @param Contact $contact
     *
     * @return Contact
     */
    protected function mockIdentity(Contact $contact): Contact
    {
        // Mock route access roles for BjyAuthorize
        $accessRoles = array_map(
            function (Access $access) {
                return $access->getAccess();
            }, $contact->getAccess()->toArray()
        );
        $this->mockAccessRoles($accessRoles);

        // Mock ZfcUserAuthentication controller plugin
        $authPluginMock = $this->getMockBuilder(ZfcUserAuthentication::class)
            ->setMethods(['getIdentity', 'hasIdentity'])
            ->getMock();

        $authPluginMock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($contact));

        $authPluginMock->expects($this->any())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        /** @var PluginManager $pluginManager */
        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
        $this->mockService(ZfcUserAuthentication::class, $authPluginMock, $pluginManager);

        return $contact;
    }

    /**
     * Reset the bjyauthorize and identity controller plugin to its original state
     */
    protected function resetIdentity()
    {
        // Reset the identity's access roles
        $this->resetAccessRoles();

        /** @var PluginManager $pluginManager */
        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
        $this->resetService(ZfcUserAuthentication::class, $pluginManager);
    }
}
