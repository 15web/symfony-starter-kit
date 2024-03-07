<?php

declare(strict_types=1);

namespace Dev\Infrastructure\ConsoleCommand;

use InvalidArgumentException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Проверяет расхождения endpoints и документации OpenApi
 */
#[AsCommand(name: 'app:openapi-routes-diff', description: 'Находит расхождения endpoints и документации openapi')]
final class OpenApiRoutesDiffCommand extends Command
{
    private const string OPEN_API_FILE_ARGUMENT = 'openApiFile';

    public function __construct(private readonly RouterInterface $router)
    {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument(
            name: self::OPEN_API_FILE_ARGUMENT,
            mode: InputArgument::REQUIRED,
            description: 'Файл OpenApi в формате yaml',
        );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $file */
        $file = $input->getArgument(self::OPEN_API_FILE_ARGUMENT);

        $io = new SymfonyStyle($input, $output);

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
        $openApiValues = (array) Yaml::parseFile($file);

        if (!\array_key_exists('paths', $openApiValues)) {
            throw new InvalidArgumentException('Invalid yaml file');
        }

        /**
         * @psalm-suppress MixedArgument
         */
        $openApiPaths = array_keys($openApiValues['paths']);

        $result = [];

        foreach ($openApiPaths as $openApiPath) {
            $result[] = '/api'.$openApiPath;
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
