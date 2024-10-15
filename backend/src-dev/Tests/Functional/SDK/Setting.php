<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\SDK;

use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class Setting extends ApiWebTestCase
{
    /**
     * @return array<int, array{
     *     id: string,
     *     type: string,
     *     value: string,
     *     isPublic: bool,
     *     createdAt: string,
     *     updatedAt: string|null,
     * }>
     */
    public static function adminList(): array
    {
        $token = User::auth();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/admin/settings',
            token: $token,
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array<int, array{
         *          id: string,
         *          type: string,
         *          value: string,
         *          isPublic: bool,
         *          createdAt: string,
         *          updatedAt: string|null,
         *     }>,
         *     pagination: array{total: int},
         * } $settings
         */
        $settings = self::jsonDecode($response->getContent());

        return $settings['data'];
    }

    /**
     * @return array<int, array{
     *     type: string,
     *     value: string,
     * }>
     */
    public static function publicList(): array
    {
        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/settings',
        );

        self::assertSuccessResponse($response);

        /** @var array{
         *     data: array<int, array{
         *          type: string,
         *          value: string
         *     }>,
         *     pagination: array{total: int},
         * } $settings
         */
        $settings = self::jsonDecode($response->getContent());

        return $settings['data'];
    }
}
