<?php

declare(strict_types=1);

namespace App\Task\Command\Comment\Add;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда добавления комментария к задаче
 */
#[ApiRequest]
final readonly class AddCommentOnTaskCommand
{
    public function __construct(public string $commentBody)
    {
        Assert::notEmpty($commentBody, 'Укажите текст комментария');
    }
}
