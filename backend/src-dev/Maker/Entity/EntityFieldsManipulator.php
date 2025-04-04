<?php

declare(strict_types=1);

namespace Dev\Maker\Entity;

use DateTimeImmutable;
use Symfony\Bundle\MakerBundle\Util\ClassSource\Model\ClassProperty;

/**
 * Генерирует сигнатуры методов на основе полей сущности
 */
final class EntityFieldsManipulator
{
    /**
     * @param list<ClassProperty> $fields
     */
    public function getConstructorProperties(array $fields): string
    {
        $properties = [];

        foreach ($fields as $field) {
            // Отступы для конструктора
            $properties[] = $field->nullable
                ? "        public ?{$this->getVariableType($field->type)} \${$field->propertyName},"
                : "        public {$this->getVariableType($field->type)} \${$field->propertyName},";
        }

        return implode(PHP_EOL, $properties).PHP_EOL;
    }

    /**
     * @param list<ClassProperty> $fields
     */
    public function getMethodParametersSignature(array $fields): string
    {
        $methodParameters = [];

        foreach ($fields as $field) {
            $methodParameters[] = $field->nullable
                ? "        ?{$this->getVariableType($field->type)} \${$field->propertyName},"
                : "        {$this->getVariableType($field->type)} \${$field->propertyName},";
        }

        return implode(PHP_EOL, $methodParameters).PHP_EOL;
    }

    /**
     * @param list<ClassProperty> $fields
     */
    public function getVariablesByFields(array $fields): string
    {
        $parameters = [];

        foreach ($fields as $field) {
            $parameters[] = "            {$field->propertyName}: \${$field->propertyName} = {$this->getTestValueByType($field->type)},";
        }

        return implode(PHP_EOL, $parameters).PHP_EOL;
    }

    /**
     * @param list<ClassProperty> $fields
     */
    public function getValuesByFields(array $fields): string
    {
        $parameters = [];

        foreach ($fields as $field) {
            $parameters[] = $this->getTestValueByType($field->type);
        }

        return implode(', ', $parameters);
    }

    /**
     * @param list<ClassProperty> $fields
     *
     * @return array<string, float|int|string|null>
     */
    public function getValuesWithType(array $fields): array
    {
        $fieldsWithData = [];

        foreach ($fields as $field) {
            $fieldsWithData[$field->propertyName] = $this->getTestValueByType($field->type);
        }

        return $fieldsWithData;
    }

    private function getTestValueByType(string $fieldType): float|int|string
    {
        return match ($fieldType) {
            'datetime_immutable' => 'new \DateTimeImmutable()',
            'float', 'double' => random_int(12, 57) / 10,
            'integer' => random_int(1, 500),
            default => "'".$this->getRandomString()."'",
        };
    }

    private function getVariableType(string $fieldType): string
    {
        return match ($fieldType) {
            'datetime_immutable' => DateTimeImmutable::class,
            'float' => 'float',
            'double' => 'double',
            'integer' => 'int',
            default => 'string',
        };
    }

    private function getRandomString(): string
    {
        $words = [
            'Lorem Ipsum',
            'Dolor Sit Amet',
            'Consectetur Adipiscing Elit',
            'Sed Do Eiusmod Tempor',
            'Incididunt Ut Labore Et Dolore',
            'Magna Aliqua',
        ];

        return $words[array_rand($words)];
    }
}
