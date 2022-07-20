<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

enum Format: string
{
    case CSV = 'csv';

    case XML = 'xml';
}
