<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\User\SignUp\Domain\User;
use App\User\SignUp\Domain\UserId;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Резолвер для айди пользователя
 */
#[AsService]
final readonly class UserIdArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private Security $security)
    {
    }

    /**
     * @return iterable<UserId>
     *
     * @throws ApiUnauthorizedException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UserId::class) {
            return [];
        }

        $user = $this->security->getUser();

        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if (!$user instanceof User) {
            throw new ApiUnauthorizedException();
        }

        return [$user->getUserId()];
    }
}
