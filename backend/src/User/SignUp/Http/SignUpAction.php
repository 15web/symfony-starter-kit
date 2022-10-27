<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\SuccessResponse;
use App\User\SignUp\Command\SignUp;
use App\User\SignUp\Command\SignUpCommand;
use App\User\SignUp\Command\UserAlreadyExistException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sign-up', methods: ['POST'])]
#[AsController]
final class SignUpAction
{
    public function __construct(private readonly SignUp $signUp)
    {
    }

    public function __invoke(SignUpCommand $signUpCommand): SuccessResponse
    {
        try {
            ($this->signUp)($signUpCommand);
        } catch (\InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        } catch (UserAlreadyExistException $e) {
            throw new ApiBadResponseException($e->getMessage(), ApiErrorCode::UserAlreadyExist);
        }

        return new SuccessResponse();
    }
}
