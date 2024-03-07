<?php

declare(strict_types=1);

namespace Dev\Rector\Rules;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symfony\Component\HttpFoundation\Request;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Заменяет строки 'GET' 'POST' на соответствующую константу из класса Symfony Request
 */
final class RequestMethodInsteadOfStringRector extends AbstractRector
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces the \'GET\' \'POST\' strings with the corresponding
        constant from the Symfony Request class',
            [new CodeSample(
                <<<'CODE_SAMPLE'
                        #[Route('/admin/articles', methods: ['POST'])]
                    CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
                        #[Route('/admin/articles', methods: [Request::METHOD_POST])]
                    CODE_SAMPLE
            )]
        );
    }

    #[Override]
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        /** @var String_ $node */
        if (
            $node->value === Request::METHOD_POST
            || $node->value === Request::METHOD_GET
        ) {
            $constName = '\Symfony\Component\HttpFoundation\Request::METHOD_'.$node->value;

            return new ConstFetch(new Name($constName));
        }

        return null;
    }
}
