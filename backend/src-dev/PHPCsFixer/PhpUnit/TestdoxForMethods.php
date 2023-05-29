<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * Добавляет методам теста аннотацию testdox
 */
final readonly class TestdoxForMethods
{
    public function __construct(
        private WhitespacesFixerConfig $whitespacesConfig,
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

            if ($this->commentHelper->isPHPDoc($tokens, $testdoxIndex)) {
                $this->updateDocBlockIfNeeded($tokens, $testdoxIndex);
            } else {
                $this->addTestdox($tokens, $testdoxIndex);
            }
        }
    }

    /**
     * @param Tokens<Token> $tokens
     */
    private function addTestdox(Tokens $tokens, int $docBlockIndex): void
    {
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
        $toInsert = [
            new Token([T_DOC_COMMENT, '/**'.$lineEnd."{$originalIndent} * @testdox TODO: Опиши что делает данный метод".
                $lineEnd."{$originalIndent} */"]),
            new Token([T_WHITESPACE, $lineEnd.$originalIndent]),
        ];
        $index = $tokens->getNextMeaningfulToken($docBlockIndex);
        $tokens->insertAt($index, $toInsert);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    private function updateDocBlockIfNeeded(Tokens $tokens, int $docBlockIndex): void
    {
        $doc = new DocBlock($tokens[$docBlockIndex]->getContent());
        if (!empty($doc->getAnnotationsOfType('testdox'))) {
            return;
        }
        $doc = $this->commentHelper->makeDocBlockMultiLineIfNeeded($doc, $tokens, $docBlockIndex);
        $lines = $this->updateDocBlock($doc, $tokens, $docBlockIndex);
        $lines = implode('', $lines);

        $tokens[$docBlockIndex] = new Token([T_DOC_COMMENT, $lines]);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    private function updateDocBlock(DocBlock $docBlock, Tokens $tokens, int $docBlockIndex): array
    {
        $lines = $docBlock->getLines();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $docBlockIndex);
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        array_splice($lines, -1, 0, $originalIndent.' *'.$lineEnd.$originalIndent.
            ' * @testdox TODO: Опиши что делает данный метод'.$lineEnd);

        return $lines;
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
        if (!$this->commentHelper->isPHPDoc($tokens, $docBlockIndex)) {
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
