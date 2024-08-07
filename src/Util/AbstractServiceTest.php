<?php

namespace Testing\Util;

use Admin\Entity\Permit\Entity;
use Admin\Service\AdminService;
use Doctrine\ORM\EntityManager;
use Mailing\Service\EmailService;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractServiceTest
 *
 * @package Testing\Controller
 */
abstract class AbstractServiceTest extends TestCase
{
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

    public function getAdminServiceMock(): AdminService
    {
        // Mock the admin service
        $adminServiceMock = $this->getMockBuilder(AdminService::class)->disableOriginalConstructor()
            ->onlyMethods(['flushPermitsByEntityAndId'])->getMock();
        $adminServiceMock->method('flushPermitsByEntityAndId');

        /** @var AdminService $adminServiceMock */
        return $adminServiceMock;
    }

    public function getEmailServiceMock(): EmailService
    {
        // Mock the email service
        $emailServiceMock = $this->getMockBuilder(EmailService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createNewWebInfoEmailBuilder', 'send'])->getMock();
        $emailServiceMock->method('createNewWebInfoEmailBuilder');
        $emailServiceMock
            ->method('send')
            ->willReturn(true);

        /** @var EmailService $emailServiceMock */
        return $emailServiceMock;
    }

    protected function getEntityManagerMock(string $entityClass = null, $repositoryMock = null): EntityManager
    {
        $mockRepository = isset($entityClass, $repositoryMock);

        $entityManagerMockBuilder = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor();
        $mockMethods = ['persist', 'flush', 'remove', 'contains', 'getClassMetadata', 'getRepository'];

        $entityManagerMockBuilder->onlyMethods($mockMethods);
        $entityManagerMock = $entityManagerMockBuilder->getMock();

        $entityManagerMock->method('persist');
        $entityManagerMock->method('flush');
        $entityManagerMock->method('remove');
        $entityManagerMock->method('contains');

        $entityRepositoryMock = $this->getMockBuilder(\Admin\Repository\Permit\Entity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMock();

        $entityRepositoryMock->method('findOneBy')->willReturn(new Entity());

        $map = [
            [Entity::class, $entityRepositoryMock],
        ];

        if ($mockRepository) {
            $map[] = [$entityClass, $repositoryMock];
        }

        $entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->returnValueMap($map));

        $metaData = new TestObjectMetadata();
        $entityManagerMock->method('getClassMetadata')
            ->willReturn($metaData);

        /** @var EntityManager $entityManagerMock */
        return $entityManagerMock;
    }
}
