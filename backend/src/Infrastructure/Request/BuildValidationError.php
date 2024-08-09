<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\AsService;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;

/**
 * Собирает массив ошибок валидации
 */
#[AsService]
final readonly class BuildValidationError
{
    /**
     * @return non-empty-list<non-empty-string>
     */
    public function __invoke(MappingError $error): array
    {
        $messages = Messages::flattenFromNode(
            node: $error->node()
        );

        $allMessages = [];
        foreach ($messages->errors() as $message) {
            $allMessages[] = $message
                ->withBody('{node_path}: {original_message}')
                ->toString();
        }

        /**
         * @var non-empty-list<non-empty-string> $allMessages
         */
        return $allMessages;
    }
}
