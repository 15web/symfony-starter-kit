<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use CuyZ\Valinor\Mapper\MappingError;

/**
 * Собирает массив ошибок валидации
 */
final readonly class BuildValidationError
{
    /**
     * @return non-empty-list<non-empty-string>
     */
    public function __invoke(MappingError $error): array
    {
        $messages = $error->messages();

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
