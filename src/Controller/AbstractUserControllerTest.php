<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Admin
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Testing\Controller;

use Admin\Entity\Access;
use Admin\Entity\Role;
use Admin\Entity\User;
use Application\Provider\Identity\AuthenticationIdentityProvider;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use BjyAuthorize\Service\Authorize as BjyAuthorize;
use Doctrine\Common\Collections\ArrayCollection;
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
abstract class AbstractUserControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var bool
     */
    protected $traceError = false;

    /**
     * Overrides default config loaded from the config files
     *
     * @var array
     */
    protected $configOverrides = [];

    /**
     * Store original objects/services here to reset them later
     *
     * @var array
     */
    private $serviceBackup = [];

    public static function generateUserDummy(array $accessRoles = []): User
    {
        $user = new User();
        $user->setId(1);
        $user->setFirstName('Test');
        $user->setLastName('Tester');

        $accessCollection = new ArrayCollection();

        foreach ($accessRoles as $id => $roleName) {
            $access = new Role();
            $access->setId($id + 1);
            $access->setDescription(ucfirst($roleName));
            $accessCollection->add($access);
        }

        $user->setAccess($accessCollection);

        return $user;
    }

    /**
     * General test setup
     */
    public function setUp(): void
    {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $defaultConfigOverrides = [];

        $configFile = __DIR__ . '/../../../../../config/application.config.php';

        $this->setApplicationConfig(
            ArrayUtils::merge(
                // Grabbing the full application + module configuration:
                file_exists($configFile)
                    ? include $configFile
                    :
                    include __DIR__ . '/../../config/application.config.php',
                $defaultConfigOverrides,
                $this->configOverrides
            )
        );
        parent::setUp();
    }

    /**
     * @return array
     */
    public function getConfigOverrides(): array
    {
        return $this->configOverrides;
    }

    /**
     * @param array $configOverrides
     *
     * @return AbstractUserControllerTest
     */
    public function setConfigOverrides(array $configOverrides): AbstractUserControllerTest
    {
        $this->configOverrides = $configOverrides;

        return $this;
    }

    /**
     * Assert route access for controller
     *
     * @param string $route
     * @param array  $accessRoles
     * @param int    $expectedStatusCode
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
     * @param string         $service
     * @param object         $mockInstance
     * @param ServiceManager $serviceManager
     */
    protected function mockService(string $service, $mockInstance, ServiceManager $serviceManager = null)
    {
        if (\is_null($serviceManager)) {
            $serviceManager = $this->getApplicationServiceLocator();
        }
        $this->serviceBackup[$service] = $serviceManager->get($service);
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($service, $mockInstance);
        $serviceManager->setAllowOverride(false);
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
     * @param string         $service
     * @param ServiceManager $serviceManager
     */
    protected function resetService(string $service, ServiceManager $serviceManager = null)
    {
        if (array_key_exists($service, $this->serviceBackup)) {
            if (\is_null($serviceManager)) {
                $serviceManager = $this->getApplicationServiceLocator();
            }
            $backup = &$this->serviceBackup[$service];
            $serviceManager->setAllowOverride(true);
            $serviceManager->setService($service, $backup);
            $serviceManager->setAllowOverride(false);
            unset($backup);
        }
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

    protected function mockIdentity(User $user): User
    {
        // Mock route access roles for BjyAuthorize
        $accessRoles = array_map(
            function (Access $access) {
                return $access->getAccess();
            },
            $user->getAccess()->toArray()
        );
        $this->mockAccessRoles($accessRoles);

        // Mock ZfcUserAuthentication controller plugin
        $authPluginMock = $this->getMockBuilder(ZfcUserAuthentication::class)
            ->setMethods(['getIdentity', 'hasIdentity'])
            ->getMock();

        $authPluginMock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($user));

        $authPluginMock->expects($this->any())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        /** @var PluginManager $pluginManager */
        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
        $this->mockService(ZfcUserAuthentication::class, $authPluginMock, $pluginManager);

        return $user;
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
