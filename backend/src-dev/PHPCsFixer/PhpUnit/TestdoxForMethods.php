<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * Добавляет методам теста атрибут testdox
 */
final readonly class TestdoxForMethods
{
    public function __construct(
        private DocCommentHelper $commentHelper
    ) {
    }

    /**
     * @param Tokens<Token> $tokens
     */
    public function addTestdoxAnnotation(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        for ($index = $endIndex - 1; $index > $startIndex; --$index) {
            if (!$this->isTestMethod($tokens, $index)) {
                continue;
            }

            $testdoxIndex = $tokens->getPrevNonWhitespace($index);
            $testdoxIndex = $tokens->getPrevNonWhitespace($testdoxIndex);

            if (!$this->commentHelper->hasTestDoxAttribute($tokens, $testdoxIndex)) {
                $this->commentHelper->addTestdoxAttribute($tokens, $testdoxIndex);
            }
        }
    }

    /**
     * @param Tokens<Token> $tokens
     */
    private function isTestMethod(Tokens $tokens, int $index): bool
    {
        if (!$this->isMethod($tokens, $index)) {
            return false;
        }

        $functionNameIndex = $tokens->getNextMeaningfulToken($index);
        $functionName = $tokens[$functionNameIndex]->getContent();

        if (str_starts_with($functionName, 'test')) {
            return true;
        }

        $docBlockIndex = $this->commentHelper->getDocBlockIndex($tokens, $index);
        if (!$this->commentHelper->isAttribute($tokens, $docBlockIndex)) {
            return false;
        }

        return str_contains($tokens[$docBlockIndex]->getContent(), '@test');
    }

    /**
     * @param Tokens<Token> $tokens
     */
    private function isMethod(Tokens $tokens, int $index): bool
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
            return false;
        }

        return !$tokensAnalyzer->isLambda($index);
    }
}
