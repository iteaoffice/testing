<?php
namespace Testing\Util;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ReflectionService;

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
