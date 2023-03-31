<?php

declare(strict_types=1);

namespace App\Article;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $config->translator()
        ->paths(['%kernel.project_dir%/src/Article/translations']);
};
