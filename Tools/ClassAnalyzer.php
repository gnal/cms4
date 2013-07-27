<?php

namespace Msi\AdminBundle\Tools;

class ClassAnalyzer
{
    public function hasTrait(\ReflectionClass $class, $traitName)
    {
        if (in_array($traitName, $class->getTraitNames())) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (false === $parentClass) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName);
    }

    public function hasMethod(\ReflectionClass $class, $methodName)
    {
        return $class->hasMethod($methodName);
    }

    public function hasProperty(\ReflectionClass $class, $propertyName)
    {
        if ($class->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (false === $parentClass) {
            return false;
        }

        return $this->hasProperty($parentClass, $propertyName);
    }
}
