<?php

declare(strict_types=1);

namespace Dev\PHPCsFixer\PhpUnit;

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * Содержит повторяющиеся методы
 */
final readonly class DocCommentHelper
{
    public function __construct(private WhitespacesFixerConfig $whitespacesConfig)
    {
    }

    /**
     * @param Tokens<Token> $tokens
     */
    public function getDocBlockIndex(Tokens $tokens, int $index): int
    {
        do {
            $index = $tokens->getPrevNonWhitespace($index);
        } while ($tokens[$index]->isGivenKind([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FINAL, T_ABSTRACT, T_COMMENT]));

        return $index;
    }

    /**
     * @param Tokens<Token> $tokens
     */
    public function isPHPDoc(Tokens $tokens, int $index): bool
    {
        return $tokens[$index]->isGivenKind(T_DOC_COMMENT);
    }

    /**
     * @param Tokens<Token> $tokens
     */
    public function makeDocBlockMultiLineIfNeeded(DocBlock $doc, Tokens $tokens, int $docBlockIndex): DocBlock
    {
        $lines = $doc->getLines();
        if ((is_countable($lines) ? \count($lines) : 0) === 1 && empty($doc->getAnnotationsOfType('testdox'))) {
            $indent = WhitespacesAnalyzer::detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
            $doc->makeMultiLine($indent, $this->whitespacesConfig->getLineEnding());

            return $doc;
        }

        return $doc;
    }
}
