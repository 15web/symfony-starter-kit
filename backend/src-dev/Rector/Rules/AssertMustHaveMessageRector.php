<?php

declare(strict_types=1);

namespace Dev\Rector\Rules;

use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Rector\AbstractRector;
use ReflectionMethod;
use Webmozart\Assert\Assert;

/**
 * Каждый ассерт должен иметь понятное сообщение об ошибке
 */
final class AssertMustHaveMessageRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        /**
         * @var StaticCall $node
         * @var Name $class
         */
        $class = $node->class;
        if ($class->name !== Assert::class) {
            return null;
        }

        /**
         * @var Identifier $name
         */
        $name = $node->name;
        $method = new ReflectionMethod($class->name, $name->name);

        // Все аргументы переданы, значит $message также указан
        if (\count($node->args) >= $method->getNumberOfParameters()) {
            return null;
        }

        /**
         * Методы могут иметь необязательные аргументы, например Assert::resource() и Assert::throws().
         * Поскольку $message всегда идет последним, то - если аргументы переданы не все,
         * то аргумент $message может быть передан только по имени.
         */
        $byName = array_filter(
            array: $node->args,
            callback: static function (Arg|VariadicPlaceholder $arg): bool {
                if (!$arg instanceof Arg) {
                    return false;
                }

                return $arg->name?->name === 'message';
            },
        );

        // Передан по имени
        if ($byName !== []) {
            return null;
        }

        /**
         * Если $message - единственный не переданный аргумент,
         * то для него необязательно указывать именованный идентификатор.
         */
        $shouldHasIdentifier = $method->getNumberOfParameters() - \count($node->args) > 1;

        $node->args[] = new Arg(
            value: new String_('TODO: Указать понятное сообщение ошибки валидации'),
            name: $shouldHasIdentifier ? new Identifier('message') : null,
        );

        return $node;
    }
}
