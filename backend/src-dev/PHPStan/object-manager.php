<?php

declare(strict_types=1);

use App\Infrastructure\Kernel;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/../../.env');

/**
 * @psalm-suppress UnnecessaryVarAnnotation
 *
 * @var string $appEnv
 */
$appEnv = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';

$kernel = new Kernel($appEnv, (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

/** @var ManagerRegistry $doctrine */
$doctrine = $kernel->getContainer()->get('doctrine');

return $doctrine->getManager();
