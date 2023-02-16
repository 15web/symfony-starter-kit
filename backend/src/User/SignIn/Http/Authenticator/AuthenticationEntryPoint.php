<?php

declare(strict_types=1);

namespace App\User\SignIn\Http\Authenticator;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\ApiException\CreateExceptionJsonResponse;
use App\Infrastructure\AsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Входная точка для генерации неавторизованных ответов (доступ запрещён).
 *
 * @see https://symfony.com/doc/current/security/access_denied_handler.html#customize-the-unauthorized-response
 */
#[AsService]
final class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly CreateExceptionJsonResponse $createExceptionJsonResponse)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return ($this->createExceptionJsonResponse)(new ApiUnauthorizedException());
    }
}
