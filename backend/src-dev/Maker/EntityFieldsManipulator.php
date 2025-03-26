<?php

declare(strict_types=1);

namespace Dev\Maker;

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
        $properties = PHP_EOL;
        foreach ($fields as $field) {
            if ($field->nullable) {
                // Отступы для конструктора
                $properties .= '        public ?'.$this->getVariableType($field->type)
                    .' $'.$field->propertyName.' = null,'.PHP_EOL;
            } else {
                $properties .= '        public '.$this->getVariableType($field->type)
                    .' $'.$field->propertyName.','.PHP_EOL;
            }
        }

        return $properties.'    ';
    }

    /**
     * @param list<ClassProperty> $fields
     */
    public function getMethodParametersSignature(array $fields): string
    {
        $methodParametersSignature = '';
        foreach ($fields as $field) {
            if ($field->nullable) {
                $methodParametersSignature .= '?'.$this->getVariableType($field->type).' $'.$field->propertyName.' = null, ';
            } else {
                $methodParametersSignature .= $this->getVariableType($field->type).' $'.$field->propertyName.', ';
            }
        }

        return substr($methodParametersSignature, 0, -2);
    }

    /**
     * @param list<ClassProperty> $fields
     */
    public function getVariablesByFields(array $fields): string
    {
        $parameters = '';
        foreach ($fields as $field) {
            $parameters .= '$'.$field->propertyName.' = '.$this->getTestValueByType($field->type).', ';
        }

        return substr($parameters, 0, -2);
    }

    /**
     * @param list<ClassProperty> $fields
     */
    public function getValuesByFields(array $fields): string
    {
        $parameters = '';
        foreach ($fields as $field) {
            $parameters .= $this->getTestValueByType($field->type).', ';
        }

        return substr($parameters, 0, -2);
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
        $value = '';

        return match ($fieldType) {
            'string', 'text' => "'".$this->getRandomString()."'",
            'datetime_immutable' => 'new \DateTimeImmutable()',
            'float', 'double' => random_int(12, 57) / 10,
            'integer' => random_int(1, 500),
            default => $value,
        };
    }

    private function getVariableType(string $fieldType): string
    {
        $type = '';

        return match ($fieldType) {
            'string', 'text' => 'string',
            'datetime_immutable' => '\DateTimeImmutable',
            'float' => 'float',
            'double' => 'double',
            'integer' => 'int',
            default => $type,
        };
    }

    private function getRandomString(): string
    {
        $words = [
            'string',
            'another_string',
            'lorem ipsum',
            'dolor sit amet',
            'next random',
            'consectetur adipiscing elit',
            'sed do eiusmod tempor',
            'incididunt ut labore et dolore',
            'magna aliqua',
        ];

        return $words[array_rand($words)];
    }
}
