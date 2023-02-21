<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\Comment;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;

/**
 * Фиксер обязательных комментариев для всех классов перед class
 */
final class ClassDocCommentFixer implements FixerInterface, WhitespacesAwareFixerInterface
{
    private WhitespacesFixerConfig $whitespacesConfig;

    public function __construct()
    {
        $this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return sprintf('ClassDocComment/%s', 'class_doc_comment');
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be a class comment before class.',
            [
                new CodeSample(
                    '<?php
final class Sample
{}
'
                ),
            ]
        );
    }

    public function getPriority(): int
    {
        return -30;
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
        $this->applyFix($tokens);
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->whitespacesConfig = $config;
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function findClasses(Tokens $tokens): iterable
    {
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isClassy()) {
                continue;
            }

            $startIndex = $tokens->getNextTokenOfKind($index, [';']);

            if ($startIndex === null) {
                return;
            }

            yield $startIndex;
        }
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function applyFix(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isClassy()) {
                continue;
            }

            $startBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);
            if (!$tokens[$startBraceIndex + 1]->isWhitespace()) {
                continue;
            }

            foreach ($this->findClasses($tokens) as $startIndex) {
                $this->addComment($tokens, $startIndex);
            }
        }
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function addComment(Tokens $tokens, int $startIndex): void
    {
        $classIndex = $tokens->getPrevTokenOfKind($startIndex, [[T_DECLARE], [T_NAMESPACE]]);
        $docBlockIndex = $this->getDocBlockIndex($tokens, $classIndex);

        if ($this->isPHPDoc($tokens, $docBlockIndex)) {
            return;
        }
        $this->createDocBlock($tokens, $docBlockIndex);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function createDocBlock(Tokens $tokens, int $docBlockIndex): void
    {
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
        $toInsert = [
            new Token([T_DOC_COMMENT, '/**'.$lineEnd."{$originalIndent} * TODO: Опиши за что отвечает данный класс, ".
                'какие проблемы решает'.$lineEnd."{$originalIndent} */"]),
            new Token([T_WHITESPACE, $lineEnd.$originalIndent]),
        ];
        $index = $tokens->getNextMeaningfulToken($docBlockIndex);
        $tokens->insertAt($index, $toInsert);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function getDocBlockIndex(Tokens $tokens, int $index): int
    {
        // Иду до объявления final|abstract|interface|enum|trait|class, если по пути встречается атрибут, останавливаюсь на нём
        // Где бы не остановился, отдаю предыдущий индекс, который не является пробелом
        $isAttribute = false;
        do {
            $index = $tokens->getNextNonWhitespace($index);

            if ($tokens[$index]->getContent() === '#[') {
                $isAttribute = true;
                $index = $tokens->getPrevNonWhitespace($index);

                break;
            }
        } while (!$tokens[$index]->isGivenKind([T_FINAL, T_ABSTRACT, T_INTERFACE, T_ENUM, T_TRAIT, T_CLASS]));

        if ($isAttribute) {
            return $index;
        }

        return $tokens->getPrevNonWhitespace($index);
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    private function isPHPDoc(Tokens $tokens, int $index): bool
    {
        return $tokens[$index]->isGivenKind(T_DOC_COMMENT);
    }

    private function getDefaultWhitespacesFixerConfig(): WhitespacesFixerConfig
    {
        static $defaultWhitespacesFixerConfig = null;

        if ($defaultWhitespacesFixerConfig === null) {
            $defaultWhitespacesFixerConfig = new WhitespacesFixerConfig('    ', "\n");
        }

        return $defaultWhitespacesFixerConfig;
    }
}
