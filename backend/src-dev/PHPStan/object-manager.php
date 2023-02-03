<?php

declare(strict_types=1);

use App\Infrastructure\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/../../.env');
// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
