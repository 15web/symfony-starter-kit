<?php

declare(strict_types=1);

namespace Dev\PHPStan;

use Doctrine\ORM\Mapping\Embeddable;
use Override;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

/**
 * Embeddable классы не обязаны иметь свойства для чтения
 *
 * Ошибка phpstan
 * Property App\Sales\Order\Domain\OrderCustomer::$fullName is never read, only written.
 * 💡 See: https://phpstan.org/developing-extensions/always-read-written-properties
 */
final readonly class EmbeddablePropertiesExtension implements ReadWritePropertiesExtension
{
    #[Override]
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        $reflectionClass = $property->getDeclaringClass()->getNativeReflection();
        $classAttributes = $reflectionClass->getAttributes();
        foreach ($classAttributes as $attribute) {
            if ($attribute->getName() === Embeddable::class) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    #[Override]
    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }
}
