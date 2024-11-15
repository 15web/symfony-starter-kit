<?php

declare(strict_types=1);

namespace App\Setting\Http\Admin;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\Setting\Command\SaveSetting;
use App\Setting\Command\SaveSettingCommand;
use App\Setting\Domain\SettingNotFoundException;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка сохранения настройки
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/settings', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class SettingSaveAction
{
    public function __construct(
        private SaveSetting $saveSetting,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        SaveSettingCommand $saveSettingCommand,
    ): ApiObjectResponse {
        try {
            $setting = ($this->saveSetting)($saveSettingCommand);

            ($this->flush)();

            $this->logger->info('Настройка сохранена', [
                'id' => $setting->getId(),
                'type' => $setting->getType()->value,
                'value' => $setting->getValue(),
                self::class => __FUNCTION__,
            ]);
        } catch (SettingNotFoundException) {
            throw new ApiNotFoundException(['Настройка не найдена']);
        }

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
