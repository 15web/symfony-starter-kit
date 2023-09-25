<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * Содержит повторяющиеся методы
 */
final readonly class DocCommentHelper
{
    // Слово TestDox находится на 4 токена ниже закрывающей скобки атрибута - ]
    private const TESTDOX_WORD_TOKEN_COUNT_BEFORE_ATTRIBUTE_CLOSE = 4;

    public function __construct(private WhitespacesFixerConfig $whitespacesConfig) {}

    public function getDocBlockIndex(Tokens $tokens, int $index): int
    {
        do {
            /** @var int $index */
            $index = $tokens->getPrevNonWhitespace($index);
        } while ($tokens[$index]->isGivenKind([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FINAL, T_ABSTRACT]));

        return $index;
    }

    public function isAttribute(Tokens $tokens, int $index): bool
    {
        return $tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE);
    }

    public function hasTestDoxAttribute(Tokens $tokens, int $index): bool
    {
        if (!$this->isAttribute($tokens, $index)) {
            return false;
        }

        return $this->isTestdoxAttribute($tokens, $index - self::TESTDOX_WORD_TOKEN_COUNT_BEFORE_ATTRIBUTE_CLOSE);
    }

    public function isTestdoxAttribute(Tokens $tokens, int $index): bool
    {
        return $tokens[$index]->getContent() === 'TestDox';
    }

    public function addTestDoxAttribute(Tokens $tokens, int $docBlockIndex): void
    {
        $lineEnd = $this->whitespacesConfig->getLineEnding();

        /** @var int $nextNonWhiteSpaceIndex */
        $nextNonWhiteSpaceIndex = $tokens->getNextNonWhitespace($docBlockIndex);

        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $nextNonWhiteSpaceIndex);
        $toInsert = [
            new Token([T_WHITESPACE,
                '#[TestDox(\'TODO: опиши что проверяется\')]',
            ]),
            new Token([T_WHITESPACE, $lineEnd.$originalIndent]),
        ];

        /** @var int $index */
        $index = $tokens->getNextMeaningfulToken($docBlockIndex);
        $tokens->insertAt($index, $toInsert);
    }
}
