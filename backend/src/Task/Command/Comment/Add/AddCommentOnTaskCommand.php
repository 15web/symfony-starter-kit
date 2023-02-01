<?php

declare(strict_types=1);

namespace App\Task\Command\Comment\Add;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда добавления комментария к задаче
 */
final class AddCommentOnTaskCommand implements ApiRequest
{
    public function __construct(public readonly string $commentBody)
    {
        Assert::notEmpty($commentBody, 'Укажите текст комментария');
    }
}
