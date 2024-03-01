<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use InvalidArgumentException;
use Override;
use PhpCsFixer\ConfigurationException\InvalidForEnvFixerConfigurationException;
use PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use PhpCsFixer\Console\Application;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\DeprecatedFixerOption;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerConfiguration\InvalidOptionsForEnvException;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;

/**
 * Добавляет всем классам тестов атрибут testdox
 */
final class TestdoxFixer implements FixerInterface, WhitespacesAwareFixerInterface, ConfigurableFixerInterface
{
    private const string EXCLUDE_KEY = 'exclude';

    private readonly TestdoxForMethods $testdoxForMethods;
    private readonly DocCommentHelper $commentHelper;
    private WhitespacesFixerConfig $whitespacesConfig;
    private ?FixerConfigurationResolverInterface $configurationDefinition = null;

    /**
     * @var array<array-key, mixed>|null
     */
    private ?array $configuration = null;

    public function __construct()
    {
        try {
            $this->configure([]);
        } catch (RequiredFixerConfigurationException) {
        }
        $this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();

        $this->commentHelper = new DocCommentHelper($this->whitespacesConfig);
        $this->testdoxForMethods = new TestdoxForMethods($this->commentHelper);
    }

    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_CLASS, T_STRING]);
    }

    #[Override]
    public function isRisky(): bool
    {
        return false;
    }

    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        if ($tokens->count() <= 0) {
            return;
        }
        if (!$this->isCandidate($tokens)) {
            return;
        }
        if (!$this->supports($file)) {
            return;
        }
        $this->applyFix($file, $tokens);
    }

    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The test must have the testdox attribute.',
            [
                new CodeSample(
                    '<?php
final class ExampleTest
{
    public function testSomething(): void
    {
    }
}
'
                ),
            ]
        );
    }

    #[Override]
    public function getName(): string
    {
        return sprintf('Testdox/%s', 'test_requires_testdox');
    }

    #[Override]
    public function getPriority(): int
    {
        return -31;
    }

    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    #[Override]
    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->whitespacesConfig = $config;
    }

    /**
     * @param array<mixed> $configuration
     */
    #[Override]
    public function configure(array $configuration): void
    {
        foreach ($this->getConfigurationDefinition()->getOptions() as $option) {
            if (!$option instanceof DeprecatedFixerOption) {
                continue;
            }

            $name = $option->getName();
            if (\array_key_exists($name, $configuration)) {
                /**
                 * @psalm-suppress DeprecatedClass
                 * @psalm-suppress InternalClass
                 * @psalm-suppress InternalMethod
                 */
                Utils::triggerDeprecation(new InvalidArgumentException(sprintf(
                    'Option "%s" for rule "%s" is deprecated and will be removed in version %d.0. %s',
                    $name,
                    $this->getName(),
                    /**
                     * @psalm-suppress InternalClass
                     * @psalm-suppress InternalMethod
                     */
                    Application::getMajorVersion() + 1,
                    str_replace('`', '"', $option->getDeprecationMessage())
                )));
            }
        }

        try {
            /** @var array<string, mixed> $configuration */
            $this->configuration = $this->getConfigurationDefinition()->resolve($configuration);
        } catch (MissingOptionsException $exception) {
            /**
             * @psalm-suppress InternalClass
             * @psalm-suppress InternalMethod
             */
            throw new RequiredFixerConfigurationException(
                $this->getName(),
                sprintf('Missing required configuration: %s', $exception->getMessage()),
                $exception
            );
        } catch (InvalidOptionsForEnvException $exception) {
            /**
             * @psalm-suppress InternalClass
             * @psalm-suppress InternalMethod
             */
            throw new InvalidForEnvFixerConfigurationException(
                $this->getName(),
                sprintf('Invalid configuration for env: %s', $exception->getMessage()),
                $exception
            );
        }
    }

    #[Override]
    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        if ($this->configurationDefinition === null) {
            $this->configurationDefinition = $this->createConfigurationDefinition();
        }

        return $this->configurationDefinition;
    }

    private function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        /**
         * @psalm-suppress InternalClass
         */
        $phpUnitTestCaseIndicator = new PhpUnitTestCaseIndicator();

        /**
         * @psalm-suppress InternalMethod
         */
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indices) {
            $this->applyPhpUnitClassFix($tokens, $file, $indices[0], $indices[1]);
        }
    }

    private function applyPhpUnitClassFix(Tokens $tokens, SplFileInfo $file, int $startIndex, int $endIndex): void
    {
        /** @var int $classIndex */
        $classIndex = $tokens->getPrevTokenOfKind($startIndex, [[T_CLASS]]);

        if (!$this->isAllowedByConfiguration($tokens, $file, $classIndex)) {
            return;
        }

        $docBlockIndex = $this->commentHelper->getDocBlockIndex($tokens, $classIndex);

        if (!$this->commentHelper->hasTestDoxAttribute($tokens, $docBlockIndex)) {
            $this->commentHelper->addTestDoxAttribute($tokens, $docBlockIndex);
        }

        $this->testdoxForMethods->addTestdoxAnnotation($tokens, $startIndex, $endIndex);
    }

    private function isAllowedByConfiguration(Tokens $tokens, SplFileInfo $file, int $i): bool
    {
        if ($this->configuration !== null) {
            /** @var string $excludeKey */
            $excludeKey = $this->configuration[self::EXCLUDE_KEY];
            if (str_contains($file->getPathname(), $excludeKey)) {
                return false;
            }
        }

        /** @var int $typeIndex */
        $typeIndex = $tokens->getPrevMeaningfulToken($i);
        if ($tokens[$typeIndex]->isGivenKind(T_FINAL)) {
            return true;
        }

        return $tokens[$typeIndex]->isGivenKind(T_ABSTRACT);
    }

    private function getDefaultWhitespacesFixerConfig(): WhitespacesFixerConfig
    {
        static $defaultWhitespacesFixerConfig = null;

        if ($defaultWhitespacesFixerConfig === null) {
            $defaultWhitespacesFixerConfig = new WhitespacesFixerConfig('    ', "\n");
        }

        /** @var WhitespacesFixerConfig $whitespacesFixerConfig */
        $whitespacesFixerConfig = $defaultWhitespacesFixerConfig;

        return $whitespacesFixerConfig;
    }

    private function createConfigurationDefinition(): FixerConfigurationResolver
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder(self::EXCLUDE_KEY, 'Excluded test namespace.'))
                ->setAllowedTypes(['string'])
                ->setNormalizer(static function (Options $options, string $value): string {
                    if (trim($value) === '') {
                        return '';
                    }

                    return $value;
                })
                ->getOption(),
        ]);
    }
}
