<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\User\Site;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Подтверждение регистрации по email')]
final class ConfirmEmailTest extends ApiWebTestCase
{
    #[TestDox('Email подтвержден')]
    public function testCorrectConfirmEmail(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );
        self::assertSuccessResponse($response);

        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );
        self::assertSuccessResponse($response);
    }

    #[TestDox('Токен удален после подтверждения')]
    public function testRemovedConfirmToken(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();

        /** @var string $confirmToken */
        $confirmToken = $email->getHeaders()->get('confirmToken')?->getBody();

        self::assertNotEmpty($confirmToken);
        self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );

        self::assertNotFound($response);
    }

    #[TestDox('Регистрация не подтверждена, неверный токен')]
    public function testNotValidConfirmToken(): void
    {
        $body = [];
        $body['email'] = 'first@example.com';
        $body['password'] = '123456';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/sign-up',
            body: $body,
        );
        self::assertSuccessResponse($response);
        self::assertEmailCount(1);

        $confirmToken = Uuid::v7();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/confirm-email/%s', $confirmToken),
        );
        self::assertNotFound($response);
    }
}
