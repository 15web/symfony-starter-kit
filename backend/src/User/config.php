<?php

declare(strict_types=1);

namespace App\User;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework
        ->rateLimiter()
        ->limiter('sign_in')
        ->policy('fixed_window')
        ->limit(3)
        ->interval('1 minute');

    $framework
        ->rateLimiter()
        ->limiter('change_password')
        ->policy('fixed_window')
        ->limit(3)
        ->interval('1 minute');
};
