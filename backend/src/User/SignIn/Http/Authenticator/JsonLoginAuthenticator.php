<?php

declare(strict_types=1);

namespace App\User\SignIn\Http\Authenticator;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\ApiException\CreateExceptionJsonResponse;
use App\Infrastructure\AsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * Аутентификатор по почте и паролю
 */
#[AsService]
final class JsonLoginAuthenticator extends AbstractAuthenticator
{
    public const SIGN_IN = 'sign-in';
    public const SIGN_IN_METHODS = [Request::METHOD_POST];
    private const EMAIL_KEY = 'email';
    private const PASSWORD_KEY = 'password';

    public function __construct(private readonly CreateExceptionJsonResponse $createExceptionJsonResponse)
    {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->attributes->get('_route') !== self::SIGN_IN) {
            return false;
        }

        return \in_array($request->getMethod(), self::SIGN_IN_METHODS, true);
    }

    /**
     * @throws ApiBadRequestException
     */
    public function authenticate(Request $request): Passport
    {
        try {
            /** @var array<string, string> $data */
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $email = $data[self::EMAIL_KEY];
            Assert::email($email);

            $password = $data[self::PASSWORD_KEY];
        } catch (Throwable $e) {
            throw new ApiBadRequestException(previous: $e);
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = 'Ошибка аутентификации';
        if ($exception instanceof BadCredentialsException) {
            $message = 'Неверный логин или пароль';
        }

        return ($this->createExceptionJsonResponse)(new ApiUnauthorizedException($message));
    }
}
