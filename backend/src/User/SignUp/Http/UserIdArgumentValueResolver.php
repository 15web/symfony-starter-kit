<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\User\SignUp\Domain\User;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;

#[AsService]
final class UserIdArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === UserId::class;
    }

    /**
     * @return iterable<UserId>
     *
     * @throws ApiUnauthorizedException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $user = $this->security->getUser();

        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if ($user instanceof User === false) {
            throw new ApiUnauthorizedException();
        }

        yield $user->getUserId();
    }
}
