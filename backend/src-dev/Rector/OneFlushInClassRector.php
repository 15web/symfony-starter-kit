<?php

declare(strict_types=1);

namespace Dev\Rector;

use App\Infrastructure\Flush;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * В классе не может быть больше одного вызова flush.
 * Идет проверка на вызовы:
 * $this->entityManager->flush() - не важно у кого вызывается, проверяется само имя метода на flush : MethodCall
 * ($this->flush)()              - у $this->flush тип должен быть App/Infrastructure/Flush          : FuncCall
 */
final class OneFlushInClassRector extends AbstractRector
{
    private const FLUSHER_CLASS = Flush::class;
    private const FLUSH_METHOD = 'flush';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove one flush if it occurs 2 or more times', [new CodeSample(
            <<<'CODE_SAMPLE'
                final class SomeClass
                {
                    public function add()
                    {
                        $this->em->flush();
                        ($this->flush)();
                    }

                    public function update()
                    {
                        $this->em->flush();
                    }
                }
                CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
                final class SomeClass
                {
                    public function add()
                    {
                        $this->em->flush();
                    }

                    public function update()
                    {
                    }
                }
                CODE_SAMPLE
        )]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;
        $hasOneFlush = false;

        /** @var Class_ $node */
        $traverse = function (Node $node, bool &$hasChanged, bool &$hasOneFlush) use (&$traverse): void {
            /**
             * @psalm-suppress NoInterfaceProperties
             * @psalm-suppress MixedAssignment
             *
             * @phpstan-ignore-next-line
             */
            foreach ($node->stmts as $key => $stmt) {
                if ($stmt instanceof Expression && ($stmt->expr instanceof FuncCall || $stmt->expr instanceof MethodCall)) {
                    $expr = $stmt->expr;
                    $type = $this->getType($expr->name);

                    if ((
                        $expr instanceof MethodCall
                        && $this->getName($expr->name) === self::FLUSH_METHOD
                    ) || (
                        $expr instanceof FuncCall
                        && method_exists($type, 'getClassName')
                        && $type->getClassName() === self::FLUSHER_CLASS
                    )) {
                        if ($hasOneFlush === false) {
                            $hasOneFlush = true;

                            continue;
                        }

                        $hasChanged = true;

                        /**
                         * @phpstan-ignore-next-line
                         *
                         * @psalm-suppress MixedArrayOffset
                         * @psalm-suppress MixedArrayAccess
                         */
                        unset($node->stmts[$key]);
                    }
                }

                /** @var callable $traverse */
                $traverse($stmt, $hasChanged, $hasOneFlush);
            }
        };

        $traverse($node, $hasChanged, $hasOneFlush);

        if ($hasChanged === false) {
            return null;
        }

        return $node;
    }
}
