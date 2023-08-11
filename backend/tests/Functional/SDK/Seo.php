<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class Seo extends ApiWebTestCase
{
    public static function create(
        string $type,
        string $identity,
        string $title,
        ?string $description,
        ?string $keywords,
    ): Response {
        $token = User::auth();

        $body = [];
        $body['type'] = $type;
        $body['identity'] = $identity;
        $body['title'] = $title;
        $body['description'] = $description;
        $body['keywords'] = $keywords;

        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request(Request::METHOD_POST, '/api/admin/seo/save', $body, token: $token);
    }
}
