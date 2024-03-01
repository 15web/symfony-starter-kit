<?php

declare(strict_types=1);

namespace App\User\SignIn\Http\Authenticator;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\ApiException\CreateExceptionJsonResponse;
use App\Infrastructure\AsService;
use App\User\SignIn\Domain\UserTokens;
use App\User\SignUp\Domain\Users;
use DomainException;
use Override;
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

/**
 * Аутентификатор по апи токену (UserToken)
 */
#[AsService]
final class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public const string TOKEN_NAME = 'X-AUTH-TOKEN';

    public function __construct(
        private readonly UserTokens $userTokens,
        private readonly Users $users,
        private readonly CreateExceptionJsonResponse $createExceptionJsonResponse,
    ) {}

    #[Override]
    public function supports(Request $request): bool
    {
        return $request->headers->has(self::TOKEN_NAME);
    }

    #[Override]
    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::TOKEN_NAME);
        if ($apiToken === null) {
            throw new CustomUserMessageAuthenticationException('Не передан токен');
        }

        if (!Uuid::isValid($apiToken)) {
            throw new CustomUserMessageAuthenticationException('Невалидный токен');
        }

        try {
            $userToken = $this->userTokens->getById(Uuid::fromString($apiToken));
        } catch (DomainException) {
            throw new CustomUserMessageAuthenticationException('Токен не найден');
        }

        try {
            $user = $this->users->getById($userToken->getUserId());
        } catch (DomainException) {
            throw new CustomUserMessageAuthenticationException('Пользователь не найден');
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    #[Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    #[Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        /**
         * Генерирует сообщение об ошибке по шаблону, например:
         * strtr('Too many failed login attempts, please try again in %minutes% minute', ['%minutes%' => $value])
         *
         * @var non-empty-string $message
         */
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return ($this->createExceptionJsonResponse)(new ApiUnauthorizedException([$message]));
    }
}
