<?php

declare(strict_types=1);

namespace App\PHPStan;

use Doctrine\ORM\Mapping\Id;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

final class GedmoUnCheckPropertiesExtension implements ReadWritePropertiesExtension
{
    private const IGNORE_ATTRIBUTES = [
        Id::class,
    ];

    private const IGNORE_ANNOTATION = [
        '@Gedmo',
    ];

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->checkAttributes($property, $propertyName) || $this->checkPhpDoc($property);
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return $this->checkAttributes($property, $propertyName) || $this->checkPhpDoc($property);
    }

    private function checkAttributes(PropertyReflection $property, string $propertyName): bool
    {
        $className = $property->getDeclaringClass()->getName();
        $reflection = new \ReflectionClass($className);
        $reflectionProperty = $reflection->getProperty($propertyName);
        $attributes = $reflectionProperty->getAttributes();

        foreach ($attributes as $attribute) {
            if (\in_array($attribute->getName(), self::IGNORE_ATTRIBUTES, true)) {
                return true;
            }
        }

        return false;
    }

    private function checkPhpDoc(PropertyReflection $property): bool
    {
        $docComment = $property->getDocComment();

        if ($docComment === null) {
            return false;
        }

        foreach (self::IGNORE_ANNOTATION as $ignoreAnnotation) {
            if (str_contains($docComment, $ignoreAnnotation)) {
                return true;
            }
        }

        return false;
    }
}
