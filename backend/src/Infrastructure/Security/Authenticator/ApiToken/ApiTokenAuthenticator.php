<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Authenticator\ApiToken;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\ApiException\CreateExceptionJsonResponse;
use App\Infrastructure\AsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public const TOKEN_NAME = 'X-AUTH-TOKEN';

    public function __construct(
        private readonly IsTokenExists $isTokenExists,
        private readonly GetEmailByTokenId $getEmailByTokenId,
        private readonly CreateExceptionJsonResponse $createExceptionJsonResponse,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::TOKEN_NAME);
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::TOKEN_NAME);
        if ($apiToken === null) {
            throw new CustomUserMessageAuthenticationException('Не передан токен');
        }

        if (Uuid::isValid($apiToken) === false) {
            throw new CustomUserMessageAuthenticationException('Невалидный токен');
        }

        $apiTokenUuid = Uuid::fromString($apiToken);

        $isTokenExists = ($this->isTokenExists)($apiTokenUuid);
        if (!$isTokenExists) {
            throw new CustomUserMessageAuthenticationException('Токен не найден');
        }

        $email = ($this->getEmailByTokenId)($apiTokenUuid);

        return new SelfValidatingPassport(
            new UserBadge($email)
        );
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        /**
         * Генерирует сообщение об ошибке по шаблону, например:
         * strtr('Too many failed login attempts, please try again in %minutes% minute', ['%minutes%' => $value])
         */
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return ($this->createExceptionJsonResponse)(new ApiUnauthorizedException($message));
    }
}
