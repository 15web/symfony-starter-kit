<?php

declare(strict_types=1);

namespace Dev\OpenApi\ConsoleCommand;

use Exception;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Собирает файл спецификации OpenApi
 */
#[AsCommand(name: 'app:generate-openapi', description: 'Собирает файлы спецификации OpenApi')]
final class GenerateOpenApiSpecCommand extends Command
{
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generate(
            resourcesDirs: [__DIR__.'/../resources/tags'],
            resultFileName: 'openapi.yaml',
        );

        (new SymfonyStyle($input, $output))
            ->success('Сборка спецификаций успешно завершена.');

        return Command::SUCCESS;
    }

    /**
     * @param list<string> $resourcesDirs
     */
    private function generate(array $resourcesDirs, string $resultFileName): void
    {
        $resultOpenApiSpec = [];

        foreach ($resourcesDirs as $resourcesDir) {
            if ($resultOpenApiSpec === []) {
                /** @var array{tags: mixed[], paths: mixed[], components: mixed[]} $baseOpenApiSpec */
                $baseOpenApiSpec = Yaml::parseFile(
                    filename: \sprintf('%s/../base.yaml', $resourcesDir),
                    flags: Yaml::PARSE_DATETIME,
                );

                /** @var array{tags: mixed[], paths: mixed[], components: mixed[]} $commonOpenApiSpec */
                $commonOpenApiSpec = Yaml::parseFile(
                    filename: \sprintf('%s/../common.yaml', $resourcesDir),
                    flags: Yaml::PARSE_DATETIME,
                );

                $resultOpenApiSpec = $this->mergeSpecs(
                    currentOpenApiSpec: $baseOpenApiSpec,
                    newOpenApiSpec: $commonOpenApiSpec,
                );
            }

            $fileNames = scandir($resourcesDir);

            if ($fileNames === false) {
                throw new Exception(\sprintf('scandir can\'t read list of directory: %s', $resourcesDir));
            }

            $fileNames = array_diff($fileNames, ['.', '..']);

            foreach ($fileNames as $fileName) {
                /** @var array{tags: mixed[], paths: mixed[], components: mixed[]} $newOpenApiSpec */
                $newOpenApiSpec = Yaml::parseFile(
                    \sprintf('%s/%s', $resourcesDir, $fileName),
                    Yaml::PARSE_DATETIME,
                );

                // todo: check duplicate keys in paths, components and tags

                /** @psalm-suppress InvalidArgument */
                $resultOpenApiSpec = $this->mergeSpecs(
                    currentOpenApiSpec: $resultOpenApiSpec,
                    newOpenApiSpec: $newOpenApiSpec,
                );
            }
        }

        if ($resultOpenApiSpec === []) {
            return;
        }

        $yaml = Yaml::dump(
            input: $resultOpenApiSpec,
            inline: 10,
            indent: 2,
            flags: Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE + Yaml::DUMP_NUMERIC_KEY_AS_STRING,
        );

        // удаляем лишние символы в ref
        $yaml = preg_replace("/([-\w.\/]+.yaml)#/ui", '#', $yaml);

        /** @var non-empty-string $yaml */
        file_put_contents(__DIR__.\sprintf('/../%s', $resultFileName), $yaml);
    }

    /**
     * @param array{tags: mixed[], paths: mixed[], components: mixed[]} $currentOpenApiSpec
     * @param array{tags: mixed[], paths: mixed[], components: mixed[]} $newOpenApiSpec
     *
     * @return array{tags: mixed[], paths: mixed[], components: mixed[]}
     */
    private function mergeSpecs(array $currentOpenApiSpec, array $newOpenApiSpec): array
    {
        $currentOpenApiSpec['tags'] = array_merge_recursive(
            $currentOpenApiSpec['tags'],
            $newOpenApiSpec['tags'],
        );

        $currentOpenApiSpec['paths'] = array_merge_recursive(
            $currentOpenApiSpec['paths'],
            $newOpenApiSpec['paths'],
        );

        $currentOpenApiSpec['components'] = array_merge_recursive(
            $currentOpenApiSpec['components'],
            $newOpenApiSpec['components'],
        );

        return $currentOpenApiSpec;
    }
}
