<?php

namespace Testing\Util;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ReflectionService;

/**
 * Class TestObjectMetadata
 *
 * @package Testing\Util
 */
class TestObjectMetadata implements ClassMetadata
{
    public function getAssociationMappedByTargetField($assocName): string
    {
        $assoc = ['children' => 'parent'];
        return $assoc[$assocName];
    }

    public function getAssociationNames(): array
    {
        return ['parent', 'children'];
    }

    public function getAssociationTargetClass($assocName): string
    {
        return __NAMESPACE__ . '\TestObject';
    }

    public function getFieldNames(): array
    {
        return ['id', 'name'];
    }

    public function getIdentifier(): array
    {
        return ['id'];
    }

    public function getReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass($this->getName());
    }

    public function getName(): string
    {
        return __NAMESPACE__ . '\TestObject';
    }

    public function getTypeOfField($fieldName): string
    {
        $types = ['id' => 'integer', 'name' => 'string'];
        return $types[$fieldName];
    }

    public function hasAssociation($fieldName): bool
    {
        return in_array($fieldName, ['parent', 'children']);
    }

    public function hasField($fieldName): bool
    {
        return in_array($fieldName, ['id', 'dateCreated', 'dateUpdated']);
    }

    public function isAssociationInverseSide($assocName): bool
    {
        return ($assocName === 'children');
    }

    public function isCollectionValuedAssociation($fieldName): bool
    {
        return ($fieldName === 'children');
    }

    public function isIdentifier($fieldName): bool
    {
        return $fieldName === 'id';
    }

    public function isSingleValuedAssociation($fieldName): bool
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
