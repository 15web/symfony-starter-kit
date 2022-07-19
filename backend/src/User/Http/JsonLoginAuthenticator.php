<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiErrorResponse;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

final class JsonLoginAuthenticator extends AbstractAuthenticator
{
    private const EMAIL_KEY = 'email';
    private const PASSWORD_KEY = 'password';
    private const CHECK_PATH = 'sign-in';

    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === self::CHECK_PATH && $request->isMethod('POST');
    }

    /**
     * @throws ApiBadRequestException
     */
    public function authenticate(Request $request): Passport
    {
        try {
            /** @var array<string, string> $data */
            $data = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $email = $data[self::EMAIL_KEY];
            Assert::email($email);

            $password = $data[self::PASSWORD_KEY];
        } catch (\Throwable $e) {
            throw new ApiBadRequestException('Неверный формат запроса');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password)
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
        $message = 'Ошибка аутентификации';
        if ($exception instanceof BadCredentialsException) {
            $message = 'Не верный логин или пароль';
        }

        $apiException = new ApiUnauthorizedException($message);
        $content = $this->serializer->serialize(
            new ApiErrorResponse($apiException->getErrorMessage(), $apiException->getApiCode()),
            JsonEncoder::FORMAT
        );

        return new JsonResponse($content, $apiException->getHttpCode(), [], true);
    }
}
