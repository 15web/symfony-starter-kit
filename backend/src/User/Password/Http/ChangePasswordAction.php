<?php

declare(strict_types=1);

namespace App\User\Password\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\CheckRateLimiter;
use App\Infrastructure\Flush;
use App\Infrastructure\Response\ApiObjectResponse;
use App\User\Password\Command\ChangePassword;
use App\User\Password\Command\ChangePasswordCommand;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\SignIn\Command\CreateToken;
use App\User\SignIn\Http\UserTokenData;
use App\User\User\Domain\AuthToken;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserPassword;
use App\User\User\Domain\UserRole;
use App\User\User\Domain\UserTokenRepository;
use App\User\User\Query\FindUser;
use App\User\User\Query\FindUserQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка смены пароля
 */
#[IsGranted(UserRole::User)]
#[Route('/change-password', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class ChangePasswordAction
{
    /**
     * @param int<min, 4> $hashCost
     */
    public function __construct(
        #[Autowire('%app.hash_cost%')]
        private int $hashCost,
        private ChangePassword $changePassword,
        private FindUser $findUser,
        private CreateToken $createToken,
        private UserTokenRepository $userTokenRepository,
        private RateLimiterFactory $changePasswordLimiter,
        private CheckRateLimiter $checkRateLimiter,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
        #[ValueResolver(ApiRequestValueResolver::class)]
        ChangePasswordRequest $request,
    ): ApiObjectResponse {
        $this->logger->info('Пользователь запросил смену пароля', [
            'userId' => $userId,
            self::class => __FUNCTION__,
        ]);

        $userData = ($this->findUser)(
            new FindUserQuery(userId: $userId),
        );

        if ($userData === null) {
            throw new ApiBadRequestException(['Ошибка аутентификации']);
        }

        /** @var non-empty-string $rateLimiterKey */
        $rateLimiterKey = (string) $userData->userId;

        $limiter = ($this->checkRateLimiter)(
            rateLimiter: $this->changePasswordLimiter,
            key: $rateLimiterKey,
        );

        $currentPassword = new UserPassword(
            cleanPassword: $request->currentPassword,
            hashCost: $this->hashCost,
        );

        if (!$currentPassword->verify($userData->password)) {
            $this->logger->info('Текущий пароль указан неверно', [
                'userId' => $userId,
            ]);

            throw new ApiBadResponseException(
                errors: ['Текущий пароль указан неверно'],
                apiCode: ApiErrorCode::Unauthenticated,
            );
        }

        $limiter->reset();

        $token = AuthToken::generate(
            hashCost: $this->hashCost,
        );

        ($this->changePassword)(
            new ChangePasswordCommand(
                userId: $userId,
                newPassword: $request->newPassword,
            )
        );

        $this->userTokenRepository->removeAllByUserId(
            new UserId($userData->userId),
        );

        ($this->createToken)(
            userId: new UserId($userData->userId),
            token: $token,
        );

        ($this->flush)();

        $this->logger->info('Пользователь сменил пароль', [
            'userId' => $userId,
        ]);

        return new ApiObjectResponse(
            data: new UserTokenData(
                (string) $token,
            ),
        );
    }
}
