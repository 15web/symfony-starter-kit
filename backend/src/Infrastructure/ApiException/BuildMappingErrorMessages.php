<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use App\Infrastructure\AsService;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Собирает массив ошибок валидации
 */
#[AsService]
final readonly class BuildMappingErrorMessages
{
    public function __construct(private TranslatorInterface $translator) {}

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
            $allMessages[] = $this->translator->trans(
                $message->withParameter('source_value', $message->node()->path())->toString()
            );
        }

        /**
         * @var non-empty-list<non-empty-string> $allMessages
         */
        return $allMessages;
    }
}
