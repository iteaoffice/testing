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

use Admin\Service\AdminService;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use General\Service\EmailService;
use PHPUnit\Framework\MockObject\MockObject;
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

    public static function setUpBeforeClass(): void
    {
        if (!defined('ITEAOFFICE_ENVIRONMENT')) {
            define('ITEAOFFICE_ENVIRONMENT', 'test');
        }

        if (!defined('ITEAOFFICE_HOST')) {
            define('ITEAOFFICE_HOST', 'test');
        }
    }

    /**
     * @return MockObject|AdminService
     */
    public function getAdminServiceMock()
    {
        //Mock the admin service
        $adminServiceMock = $this->getMockBuilder(AdminService::class)->disableOriginalConstructor()
            ->setMethods(['flushPermitsByEntityAndId',])->getMock();
        $adminServiceMock->method('flushPermitsByEntityAndId');

        return $adminServiceMock;
    }

    public function getEmailServiceMock()
    {
        //Mock the email service
        $emailServiceMock = $this->getMockBuilder(EmailService::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWebInfo', 'send', 'setSender', 'addTo'])->getMock();
        $emailServiceMock->method('setWebInfo');
        $emailServiceMock->method('setSender');
        $emailServiceMock->method('addTo');
        $emailServiceMock
            ->method('send')
            ->willReturn(true);

        return $emailServiceMock;
    }

    /**
     * @param string|null $entityClass
     * @param null $repositoryMock
     * @return MockObject|EntityManagerInterface
     */
    protected function getEntityManagerMock(string $entityClass = null, $repositoryMock = null)
    {
        $mockRepository = isset($entityClass, $repositoryMock);

        $entityManagerMockBuilder = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor();
        $mockMethods = ['persist', 'flush', 'remove', 'contains', 'getClassMetadata'];
        if ($mockRepository) { // Mock the getRepository method
            $mockMethods[] = 'getRepository';
        }
        $entityManagerMockBuilder->onlyMethods($mockMethods);
        $entityManagerMock = $entityManagerMockBuilder->getMock();

        $entityManagerMock->method('persist');
        $entityManagerMock->method('flush');
        $entityManagerMock->method('remove');
        $entityManagerMock->method('contains');

        $metaData = new TestObjectMetadata();
        $entityManagerMock->method('getClassMetadata')
            ->willReturn($metaData);

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
