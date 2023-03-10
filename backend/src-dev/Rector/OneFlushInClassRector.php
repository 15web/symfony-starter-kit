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

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasOneFlush = false;
        foreach ($node->getMethods() as $method) {
            if (!is_iterable($method->stmts)) {
                break;
            }

            foreach ($method->stmts as $key => $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }

                $expr = $stmt->expr;
                if (!$expr instanceof FuncCall && !$expr instanceof MethodCall) {
                    continue;
                }

                if ($expr instanceof FuncCall && $this->getType($expr->name)->getClassName() === self::FLUSHER_CLASS) {
                    if ($hasOneFlush === false) {
                        $hasOneFlush = true;

                        continue;
                    }
                    $this->nodeRemover->removeStmt($method, $key);
                }

                if ($expr instanceof MethodCall && $this->getName($expr->name) === self::FLUSH_METHOD) {
                    if ($hasOneFlush === false) {
                        $hasOneFlush = true;

                        continue;
                    }
                    $this->nodeRemover->removeStmt($method, $key);
                }
            }
        }

        return $node;
    }
}
