<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;

/**
 * Собирает массив ошибок валидации
 */
final readonly class BuildValidationError
{
    private TranslationMessageFormatter $messageFormatter;

    public function __construct()
    {
        $this->messageFormatter = (new TranslationMessageFormatter())
            ->withTranslations(ValidationErrorTranslation::TRANSLATIONS);
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function __invoke(MappingError $error): array
    {
        $messages = Messages::flattenFromNode(
            node: $error->node(),
        );

        $allMessages = [];

        foreach ($messages->errors() as $node) {
            $message = $this->messageFormatter
                ->format($node)
                ->toString();

            if (!$node->node()->isRoot()) {
                $message = \sprintf('%s: %s', $node->node()->path(), $message);
            }

            $allMessages[] = $message;
        }

        /** @var non-empty-list<non-empty-string> $allMessages */
        return $allMessages;
    }
}
