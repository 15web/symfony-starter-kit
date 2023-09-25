<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * Добавляет методам теста атрибут testdox
 */
final readonly class TestdoxForMethods
{
    public function __construct(
        private DocCommentHelper $commentHelper
    ) {}

    public function addTestdoxAnnotation(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        for ($index = $endIndex - 1; $index > $startIndex; --$index) {
            if (!$this->isTestMethod($tokens, $index)) {
                continue;
            }

            /** @var int $testdoxIndex */
            $testdoxIndex = $tokens->getPrevNonWhitespace($index);

            /** @var int $testdoxIndex */
            $testdoxIndex = $tokens->getPrevNonWhitespace($testdoxIndex);

            if (!$this->commentHelper->hasTestDoxAttribute($tokens, $testdoxIndex)) {
                $this->commentHelper->addTestDoxAttribute($tokens, $testdoxIndex);
            }
        }
    }

    private function isTestMethod(Tokens $tokens, int $index): bool
    {
        if (!$this->isMethod($tokens, $index)) {
            return false;
        }

        /** @var int $functionNameIndex */
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

    private function isMethod(Tokens $tokens, int $index): bool
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
            return false;
        }

        /**
         * @psalm-suppress InternalMethod
         */
        return !$tokensAnalyzer->isLambda($index);
    }
}
