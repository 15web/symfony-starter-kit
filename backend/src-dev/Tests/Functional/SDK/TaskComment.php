<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class TaskComment extends ApiWebTestCase
{
    public static function create(string $commentText, string $taskId, string $token): Response
    {
        $body = [];
        $body['commentBody'] = $commentText;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/add-comment', $taskId),
            body: $body,
            token: $token,
        );
    }
}
