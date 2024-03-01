<?php

declare(strict_types=1);

namespace Dev\Rector\Rules;

use Override;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Обязывает указывать резолверы в аргументах экшенов
 */
final class ResolversInActionRector extends AbstractRector
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Obliges to specify ValueResolver in the arguments of actions', [new CodeSample(
            <<<'CODE_SAMPLE'
                    public function __invoke(
                        Uuid $id,
                        UserId $userId,
                    ): TaskData {}
                CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
                    public function __invoke(
                        #[ValueResolver('TODO: Добавь резолвер')]
                        Uuid $id,
                        #[ValueResolver('TODO: Добавь резолвер')]
                        UserId $userId,
                    ): TaskData {}
                CODE_SAMPLE
        )]);
    }

    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        /** @var Class_ $node */
        if (!$this->hasAsControllerAttribute($node)) {
            return null;
        }

        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() === '__invoke') {
                foreach ($method->params as $param) {
                    if ($this->hasValueResolverAttribute($param)) {
                        continue;
                    }

                    $param->attrGroups[] = new AttributeGroup([
                        new Attribute(
                            new Name('ValueResolver(\'TODO: добавь резолвер\')')
                        ),
                    ]);
                }
            }
        }

        return $node;
    }

    /**
     * Проверяет имеет ли класс атрибут #[AsController]
     *
     * @param Class_ $classNode
     */
    private function hasAsControllerAttribute(Node $classNode): bool
    {
        foreach ($classNode->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === AsController::class) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Имеет ли параметр метода атрибут #[ValueResolver]
     */
    private function hasValueResolverAttribute(Param $param): bool
    {
        foreach ($param->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === ValueResolver::class) {
                    return true;
                }
            }
        }

        return false;
    }
}
