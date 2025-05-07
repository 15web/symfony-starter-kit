<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

/**
 * Перевод ошибок валидации
 */
final readonly class ValidationErrorTranslation
{
    public const array TRANSLATIONS = [
        'Unexpected key(s) {keys}, expected {expected_keys}.' => [
            'en' => 'Недопустимые аргументы {keys}, допустимые: {expected_keys}.',
        ],
        'Value {source_value} does not match any of {allowed_values}.' => [
            'en' => 'Значение {source_value} не соответствует ни одному из {allowed_values}.',
        ],
        'Value {source_value} does not match any of {allowed_types}.' => [
            'en' => 'Значение {source_value} не соответствует ни одному из типов: {allowed_types}.',
        ],
        'Cannot be empty and must be filled with a value matching any of {allowed_types}.' => [
            'en' => 'Не может быть пустым и должно соответствовать одному из типов: {allowed_types}.',
        ],
        'Value {source_value} does not match type {expected_type}.' => [
            'en' => 'Значение {source_value} не соответствует типу {expected_type}.',
        ],
        'Value {source_value} does not match {expected_value}.' => [
            'en' => 'Значение {source_value} не соответствует {expected_value}.',
        ],
        'Value {source_value} does not match boolean value {expected_value}.' => [
            'en' => 'Значение {source_value} не соответствует булевому значению {expected_value}.',
        ],
        'Value {source_value} does not match float value {expected_value}.' => [
            'en' => 'Значение {source_value} не соответствует числу с плавающей запятой {expected_value}.',
        ],
        'Value {source_value} does not match integer value {expected_value}.' => [
            'en' => 'Значение {source_value} не соответствует целому числу {expected_value}.',
        ],
        'Value {source_value} does not match string value {expected_value}.' => [
            'en' => 'Значение {source_value} не соответствует строке {expected_value}.',
        ],
        'Value {source_value} is not null.' => [
            'en' => 'Значение {source_value} должно быть null.',
        ],
        'Value {source_value} is not a valid boolean.' => [
            'en' => 'Значение {source_value} невалидное булево.',
        ],
        'Value {source_value} is not a valid float.' => [
            'en' => 'Значение {source_value} невалидное число с плавающей запятой.',
        ],
        'Value {source_value} is not a valid integer.' => [
            'en' => 'Значение {source_value} невалидное число.',
        ],
        'Value {source_value} is not a valid string.' => [
            'en' => 'Значение {source_value} невалидная строка.',
        ],
        'Value {source_value} is not a valid negative integer.' => [
            'en' => 'Значение {source_value} невалидное отрицательное число.',
        ],
        'Value {source_value} is not a valid positive integer.' => [
            'en' => 'Значение {source_value} невалидное положительное число.',
        ],
        'Value {source_value} is not a valid non-empty string.' => [
            'en' => 'Значение {source_value} невалидная непустая строка.',
        ],
        'Value {source_value} is not a valid numeric string.' => [
            'en' => 'Значение {source_value} невалидная строка из цифр.',
        ],
        'Value {source_value} is not a valid integer between {min} and {max}.' => [
            'en' => 'Значение {source_value} невалидное число от {min} до {max}.',
        ],
        'Value {source_value} is not a valid timezone.' => [
            'en' => 'Значение {source_value} невалидная временная зона.',
        ],
        'Value {source_value} is not a valid class string.' => [
            'en' => 'Значение {source_value} невалидное имя класса.',
        ],
        'Value {source_value} is not a valid class string of `{expected_class_type}`.' => [
            'en' => 'Значение {source_value} невалидное имя класса `{expected_class_type}`.',
        ],
        'Invalid value {source_value}.' => [
            'en' => 'Невалидное значение {source_value}.',
        ],
        'Invalid value {source_value}, it matches at least two types from union.' => [
            'en' => 'Невалидное значение {source_value}, соответствует как минимум двум типам из списка.',
        ],
        'Invalid value {source_value}, it matches at least two types from {allowed_types}.' => [
            'en' => 'Невалидное значение {source_value}, соответствует как минимум двум типам из {allowed_types}.',
        ],
        'Invalid sequential key {key}, expected {expected}.' => [
            'en' => 'Невалидный последовательный ключ {key}, должен быть {expected}.',
        ],
        'Cannot be empty.' => [
            'en' => 'Не может быть пустым.',
        ],
        'Cannot be empty and must be filled with a value matching type {expected_type}.' => [
            'en' => 'Не может быть пустым и должно соответствовать типу {expected_type}.',
        ],
        'Key {key} does not match type {expected_type}.' => [
            'en' => 'Ключ {key} не соответствует типу {expected_type}.',
        ],
        'Value {source_value} does not match a valid date format.' => [
            'en' => 'Значение {source_value} имеет невалидный формат дата.',
        ],
        'Value {source_value} does not match any of the following formats: {formats}.' => [
            'en' => 'Значение {source_value} не соответствует ни одному из форматов: {formats}.',
        ],
    ];
}
