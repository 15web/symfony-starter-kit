<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

/**
 * Форматы экспорта
 */
enum Format: string
{
    case CSV = 'csv';

    case XML = 'xml';
}
