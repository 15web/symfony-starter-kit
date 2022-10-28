<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\Security\Authenticator\ApiToken\ApiTokenAuthenticator;
use App\Infrastructure\SuccessResponse;
use App\User\SignIn\Command\DeleteToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/logout', methods: ['GET'])]
#[AsController]
final class LogoutAction
{
    public function __construct(private readonly DeleteToken $deleteToken)
    {
    }

    public function __invoke(Request $request): SuccessResponse
    {
        $apiToken = $request->headers->get(ApiTokenAuthenticator::TOKEN_NAME);
        if ($apiToken === null) {
            throw new ApiUnauthorizedException('Отсутствует токен в заголовках');
        }

        ($this->deleteToken)(Uuid::fromString($apiToken));

        return new SuccessResponse();
    }
}
