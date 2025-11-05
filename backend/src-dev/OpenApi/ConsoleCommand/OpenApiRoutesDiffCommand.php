<?php

declare(strict_types=1);

namespace Dev\OpenApi\ConsoleCommand;

use InvalidArgumentException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Проверяет расхождения endpoints и документации OpenApi
 */
#[AsCommand(name: 'app:openapi-routes-diff', description: 'Находит расхождения endpoints и документации openapi')]
final readonly class OpenApiRoutesDiffCommand
{
    private const string OPEN_API_FILE_ARGUMENT = 'openApiFile';

    public function __construct(private RouterInterface $router) {}

    public function __invoke(
        #[Argument(description: 'Файл OpenApi в формате yaml', name: self::OPEN_API_FILE_ARGUMENT)]
        string $openApiFile,
        SymfonyStyle $io,
    ): int {
        $file = $openApiFile;
        $openApiPaths = $this->getOpenApiPaths($file);
        $appPaths = $this->getAppPaths();
        $missingAppPaths = array_diff($openApiPaths, $appPaths);
        $missingOpenApiPaths = array_diff($appPaths, $openApiPaths);
        if ($missingAppPaths === [] && $missingOpenApiPaths === []) {
            $io->success('Расхождения endpoints и документации openapi не найдены');

            return Command::SUCCESS;
        }

        if ($missingAppPaths !== []) {
            $io->info(['Найдены endpoints, которые не реализованы в приложении:', ...$missingAppPaths]);
        }

        if ($missingOpenApiPaths !== []) {
            $io->info(["Найдены endpoints, которые не описаны в {$file}:", ...$missingOpenApiPaths]);
        }

        return Command::FAILURE;
    }

    /**
     * @return string[]
     */
    private function getOpenApiPaths(string $file): array
    {
        /**
         * @var array{
         *     paths?: array<string, array<array-key, mixed>>
         * } $openApiValues
         */
        $openApiValues = (array) Yaml::parseFile($file);

        if (!\array_key_exists('paths', $openApiValues)) {
            throw new InvalidArgumentException('Invalid yaml file');
        }

        $openApiPaths = array_keys($openApiValues['paths']);

        $result = [];

        foreach ($openApiPaths as $openApiPath) {
            $result[] = \sprintf('/api/%s', ltrim($openApiPath, '/'));
        }

        return $result;
    }

    /**
     * @return string[]
     */
    private function getAppPaths(): array
    {
        $routes = $this->router->getRouteCollection();

        $result = [];
        foreach ($routes as $route) {
            $path = $route->getPath();
            if (!str_contains($path, 'api')) {
                continue;
            }
            $result[] = $route->getPath();
        }

        return $result;
    }
}
