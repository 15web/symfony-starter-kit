<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use InvalidArgumentException;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\ConfigurationException\InvalidForEnvFixerConfigurationException;
use PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use PhpCsFixer\Console\Application;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
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
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;

/**
 * Добавляет всем классам тестов аннотацию testdox
 */
final class TestdoxFixer implements FixerInterface, WhitespacesAwareFixerInterface, ConfigurableFixerInterface
{
    private const EXCLUDE_KEY = 'exclude';

    private readonly TestdoxForMethods $testdoxForMethods;
    private readonly DocCommentHelper $commentHelper;
    private WhitespacesFixerConfig $whitespacesConfig;
    private ?FixerConfigurationResolverInterface $configurationDefinition = null;
    private ?array $configuration = null;

    public function __construct()
    {
        try {
            $this->configure([]);
        } catch (RequiredFixerConfigurationException) {
        }
        $this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();

        $this->commentHelper = new DocCommentHelper($this->whitespacesConfig);
        $this->testdoxForMethods = new TestdoxForMethods($this->whitespacesConfig, $this->commentHelper);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_CLASS, T_STRING]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
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

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The test must have the testdox annotation.',
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

    public function getName(): string
    {
        return sprintf('Testdox/%s', 'test_requires_testdox');
    }

    public function getPriority(): int
    {
        return 67;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->whitespacesConfig = $config;
    }

    public function configure(array $configuration): void
    {
        foreach ($this->getConfigurationDefinition()->getOptions() as $option) {
            if (!$option instanceof DeprecatedFixerOption) {
                continue;
            }

            $name = $option->getName();
            if (\array_key_exists($name, $configuration)) {
                Utils::triggerDeprecation(new InvalidArgumentException(sprintf(
                    'Option "%s" for rule "%s" is deprecated and will be removed in version %d.0. %s',
                    $name,
                    $this->getName(),
                    Application::getMajorVersion() + 1,
                    str_replace('`', '"', $option->getDeprecationMessage())
                )));
            }
        }

        try {
            $this->configuration = $this->getConfigurationDefinition()->resolve($configuration);
        } catch (MissingOptionsException $exception) {
            throw new RequiredFixerConfigurationException(
                $this->getName(),
                sprintf('Missing required configuration: %s', $exception->getMessage()),
                $exception
            );
        } catch (InvalidOptionsForEnvException $exception) {
            throw new InvalidForEnvFixerConfigurationException(
                $this->getName(),
                sprintf('Invalid configuration for env: %s', $exception->getMessage()),
                $exception
            );
        } catch (ExceptionInterface $exception) {
            throw new InvalidFixerConfigurationException(
                $this->getName(),
                sprintf('Invalid configuration: %s', $exception->getMessage()),
                $exception
            );
        }
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        if ($this->configurationDefinition === null) {
            $this->configurationDefinition = $this->createConfigurationDefinition();
        }

        return $this->configurationDefinition;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $phpUnitTestCaseIndicator = new PhpUnitTestCaseIndicator();

        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indices) {
            $this->applyPhpUnitClassFix($tokens, $file, $indices[0], $indices[1]);
        }
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function applyPhpUnitClassFix(Tokens $tokens, SplFileInfo $file, int $startIndex, int $endIndex): void
    {
        $classIndex = $tokens->getPrevTokenOfKind($startIndex, [[T_CLASS]]);

        if (!$this->isAllowedByConfiguration($tokens, $file, $classIndex)) {
            return;
        }

        $docBlockIndex = $this->commentHelper->getDocBlockIndex($tokens, $classIndex);

        if ($this->commentHelper->isPHPDoc($tokens, $docBlockIndex)) {
            $this->updateDocBlockIfNeeded($tokens, $docBlockIndex);
        } else {
            $this->createDocBlock($tokens, $docBlockIndex);
        }

        $this->testdoxForMethods->addTestdoxAnnotation($tokens, $startIndex, $endIndex);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function isAllowedByConfiguration(Tokens $tokens, SplFileInfo $file, int $i): bool
    {
        if (str_contains($file->getPathname(), (string) $this->configuration[self::EXCLUDE_KEY])) {
            return false;
        }

        $typeIndex = $tokens->getPrevMeaningfulToken($i);
        if ($tokens[$typeIndex]->isGivenKind(T_FINAL)) {
            return true;
        }

        return $tokens[$typeIndex]->isGivenKind(T_ABSTRACT);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function createDocBlock(Tokens $tokens, int $docBlockIndex): void
    {
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
        $toInsert = [
            new Token([T_DOC_COMMENT,
                '/**'.$lineEnd."{$originalIndent} * @testdox TODO: опиши что проверяет класс".$lineEnd.
                "{$originalIndent} */"]),
            new Token([T_WHITESPACE, $lineEnd.$originalIndent]),
        ];
        $index = $tokens->getNextMeaningfulToken($docBlockIndex);
        $tokens->insertAt($index, $toInsert);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function updateDocBlockIfNeeded(Tokens $tokens, int $docBlockIndex): void
    {
        $doc = new DocBlock($tokens[$docBlockIndex]->getContent());
        if (!empty($doc->getAnnotationsOfType('testdox'))) {
            return;
        }
        $doc = $this->commentHelper->makeDocBlockMultiLineIfNeeded($doc, $tokens, $docBlockIndex);
        $lines = $this->addTestdoxAnnotation($doc, $tokens, $docBlockIndex);
        $lines = implode('', $lines);

        $tokens[$docBlockIndex] = new Token([T_DOC_COMMENT, $lines]);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     *
     * @return Line[]
     */
    private function addTestdoxAnnotation(DocBlock $docBlock, Tokens $tokens, int $docBlockIndex): array
    {
        $lines = $docBlock->getLines();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $docBlockIndex);
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        array_splice($lines, -1, 0, $originalIndent.' *'.$lineEnd.$originalIndent.
            ' * @testdox TODO: опиши что проверяет класс'.$lineEnd);

        return $lines;
    }

    private function getDefaultWhitespacesFixerConfig(): WhitespacesFixerConfig
    {
        static $defaultWhitespacesFixerConfig = null;

        if ($defaultWhitespacesFixerConfig === null) {
            $defaultWhitespacesFixerConfig = new WhitespacesFixerConfig('    ', "\n");
        }

        return $defaultWhitespacesFixerConfig;
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