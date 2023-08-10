<?php

declare(strict_types=1);

namespace Dev\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symfony\Component\HttpFoundation\Request;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Заменяет строки 'GET' 'POST' на соответствующую констансту из класса Symfony Request
 */
final class RequestMethodInsteadOfStringRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces the \'GET\' \'POST\' strings with the corresponding
        constant from the Symfony Request class',
            [new CodeSample(
                <<<'CODE_SAMPLE'
                        #[Route('/admin/articles/create', methods: ['POST'])]
                    CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
                        #[Route('/admin/articles/create', methods: [Request::METHOD_POST])]
                    CODE_SAMPLE
            )]
        );
    }

    public function getNodeTypes(): array
    {
        return [String_::class];
    }

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
