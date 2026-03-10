<?php

namespace Testing\Util;

use Admin\Entity\Permit\Entity;
use Admin\Service\AdminService;
use Doctrine\ORM\EntityManager;
use Mailing\Service\EmailService;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
        $adminServiceMock = $this->createStub(AdminService::class);
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

    protected function getEntityManagerMock(
        ?string                     $entityClass = null,
        null|MockObject|MockBuilder|Stub $repositoryMock = null
    ): EntityManager
    {
        $mockRepository = isset($entityClass, $repositoryMock);

        $entityManagerMock = $this->createStub(EntityManager::class);

        $entityManagerMock->method('persist');
        $entityManagerMock->method('flush');
        $entityManagerMock->method('remove');
        $entityManagerMock->method('contains');

        $entityRepositoryMock = $this->createStub(\Admin\Repository\Permit\Entity::class);
        $entityRepositoryMock->method('findOneBy')->willReturn(new Entity());

        $map = [
            [Entity::class, $entityRepositoryMock],
        ];

        if ($mockRepository) {
            $map[] = [$entityClass, $repositoryMock];
        }

        $entityManagerMock->method('getRepository')
            ->willReturnMap($map);

        $metaData = new TestObjectMetadata();
        $entityManagerMock->method('getClassMetadata')
            ->willReturn($metaData);

        /** @var EntityManager $entityManagerMock */
        return $entityManagerMock;
    }
}
