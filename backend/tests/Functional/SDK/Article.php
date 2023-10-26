<?php

declare(strict_types=1);

namespace App\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class Article extends ApiWebTestCase
{
    public static function create(string $title, string $alias, string $content, string $token): Response
    {
        $body = [];
        $body['title'] = $title;
        $body['alias'] = $alias;
        $body['body'] = $content;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request(Request::METHOD_POST, '/api/admin/articles', $body, token: $token);
    }

    public static function createAndReturnId(string $title, string $alias, string $content, string $token): string
    {
        $body = [];
        $body['title'] = $title;
        $body['alias'] = $alias;
        $body['body'] = $content;
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(Request::METHOD_POST, '/api/admin/articles', $body, token: $token);

        /** @var array{
         *     id: string,
         *     title: string,
         *     alias: string,
         *     body: string,
         *     createdAt: string,
         *     updatedAt: string|null,
         * } $article */
        $article = self::jsonDecode($response->getContent());

        return $article['id'];
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     alias: string,
     *     body: string,
     *     createdAt: string,
     *     updatedAt: string|null,
     * }>
     */
    public static function list(string $token): array
    {
        $response = self::request(Request::METHOD_GET, '/api/admin/articles', token: $token);

        self::assertSuccessResponse($response);

        /** @var array<int, array{
         *     id: string,
         *     title: string,
         *     alias: string,
         *     body: string,
         *     createdAt: string,
         *     updatedAt: string|null,
         * }> $articles */
        $articles = self::jsonDecode($response->getContent());

        return $articles;
    }
}
