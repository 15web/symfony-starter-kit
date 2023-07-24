<?php

declare(strict_types=1);

namespace Dev\Maker\Vendor;

use Exception;
use LogicException;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\PhpCompatUtil;
use Symfony\Bundle\MakerBundle\Validator;

/**
 * Подгружает наши ресурсы (шаблоны классов)
 * Полная копия Generator(MakerBundle) за исключением addOperation метода, где указывается наша директория с ресурсами
 *
 * @psalm-suppress
 */
final class CustomGenerator
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $pendingOperations = [];

    public function __construct(
        private readonly FileManager $fileManager,
        private string $namespacePrefix,
        ?PhpCompatUtil $phpCompatUtil = null,
    ) {
        $this->namespacePrefix = trim($namespacePrefix, '\\');

        if ($phpCompatUtil !== null) {
            trigger_deprecation('symfony/maker-bundle', 'v1.44.0', 'Initializing Generator while providing an instance of PhpCompatUtil is deprecated.');
        }
    }

    /**
     * Generate a new file for a class from a template.
     *
     * @param string $className The fully-qualified class name
     * @param string $templateName Template name in Resources/skeleton to use
     * @param array<string, mixed> $variables Array of variables to pass to the template
     *
     * @return string The path where the file will be created
     *
     * @throws Exception
     */
    public function generateClass(string $className, string $templateName, array $variables = []): string
    {
        /**
         * @psalm-suppress InternalMethod
         */
        $targetPath = $this->fileManager->getRelativePathForFutureClass($className);

        if ($targetPath === null) {
            throw new LogicException(sprintf('Could not determine where to locate the new class "%s", maybe try with a full namespace like "\\My\\Full\\Namespace\\%s"', $className, Str::getShortClassName($className)));
        }

        $variables = [...$variables, 'class_name' => Str::getShortClassName($className), 'namespace' => Str::getNamespace($className)];

        $this->addOperation($targetPath, $templateName, $variables);

        return $targetPath;
    }

    public function dumpFile(string $targetPath, string $contents): void
    {
        $this->pendingOperations[$targetPath] = [
            'contents' => $contents,
        ];
    }

    public function getFileContentsForPendingOperation(string $targetPath): string
    {
        if (!isset($this->pendingOperations[$targetPath])) {
            throw new RuntimeCommandException(sprintf('File "%s" is not in the Generator\'s pending operations', $targetPath));
        }

        /** @var string $templatePath */
        $templatePath = $this->pendingOperations[$targetPath]['template'];

        /** @var array<string, mixed> $parameters */
        $parameters = $this->pendingOperations[$targetPath]['variables'];

        /**
         * @psalm-suppress InternalMethod
         */
        $templateParameters = [...$parameters, 'relative_path' => $this->fileManager->relativizePath($targetPath)];

        /**
         * @psalm-suppress InternalMethod
         */
        return $this->fileManager->parseTemplate($templatePath, $templateParameters);
    }

    /**
     * Creates a helper object to get data about a class name.
     *
     * Examples:
     *
     *      // App\Entity\FeaturedProduct
     *      $gen->createClassNameDetails('FeaturedProduct', 'Entity');
     *      $gen->createClassNameDetails('featured product', 'Entity');
     *
     *      // App\Controller\FooController
     *      $gen->createClassNameDetails('foo', 'Controller', 'Controller');
     *
     *      // App\Controller\Foo\AdminController
     *      $gen->createClassNameDetails('Foo\\Admin', 'Controller', 'Controller');
     *
     *      // App\Security\Voter\CoolVoter
     *      $gen->createClassNameDetails('Cool', 'Security\Voter', 'Voter');
     *
     *      // Full class names can also be passed. Imagine the user has an autoload
     *      // rule where Cool\Stuff lives in a "lib/" directory
     *      // Cool\Stuff\BalloonController
     *      $gen->createClassNameDetails('Cool\\Stuff\\Balloon', 'Controller', 'Controller');
     *
     * @param string $name The short "name" that will be turned into the class name
     * @param string $namespacePrefix Recommended namespace where this class should live, but *without* the "App\\" part
     * @param string $suffix Optional suffix to guarantee is on the end of the class
     */
    public function createClassNameDetails(string $name, string $namespacePrefix, string $suffix = '', string $validationErrorMessage = ''): ClassNameDetails
    {
        $fullNamespacePrefix = $this->namespacePrefix.'\\'.$namespacePrefix;
        if ($name[0] === '\\') {
            // class is already "absolute" - leave it alone (but strip opening \)
            $className = substr($name, 1);
        } else {
            $className = rtrim($fullNamespacePrefix, '\\').'\\'.Str::asClassName($name, $suffix);
        }

        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        Validator::validateClassName($className, $validationErrorMessage);

        // if this is a custom class, we may be completely different than the namespace prefix
        // the best way can do, is find the PSR4 prefix and use that
        if (!str_starts_with($className, $fullNamespacePrefix)) {
            /**
             * @psalm-suppress InternalMethod
             */
            $fullNamespacePrefix = $this->fileManager->getNamespacePrefixForClass($className);
        }

        return new ClassNameDetails($className, $fullNamespacePrefix, $suffix);
    }

    /**
     * Actually writes and file changes that are pending.
     */
    public function writeChanges(): void
    {
        foreach ($this->pendingOperations as $targetPath => $templateData) {
            if (isset($templateData['contents'])) {
                /** @var string $content */
                $content = $templateData['contents'];

                /**
                 * @psalm-suppress InternalMethod
                 */
                $this->fileManager->dumpFile($targetPath, $content);

                continue;
            }

            /**
             * @psalm-suppress InternalMethod
             */
            $this->fileManager->dumpFile(
                $targetPath,
                $this->getFileContentsForPendingOperation($targetPath)
            );
        }

        $this->pendingOperations = [];
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function addOperation(string $targetPath, string $templateName, array $variables): void
    {
        /**
         * @psalm-suppress InternalMethod
         */
        if ($this->fileManager->fileExists($targetPath)) {
            throw new RuntimeCommandException(sprintf('The file "%s" can\'t be generated because it already exists.', $this->fileManager->relativizePath($targetPath)));
        }

        /**
         * @psalm-suppress InternalMethod
         */
        $variables['relative_path'] = $this->fileManager->relativizePath($targetPath);

        $templatePath = $templateName;
        if (!file_exists($templatePath)) {
            $templatePath = __DIR__.'/../Resources/skeleton/'.$templateName;

            if (!file_exists($templatePath)) {
                throw new Exception(sprintf('Cannot find template "%s"', $templateName));
            }
        }

        $this->pendingOperations[$targetPath] = [
            'template' => $templatePath,
            'variables' => $variables,
        ];
    }
}
