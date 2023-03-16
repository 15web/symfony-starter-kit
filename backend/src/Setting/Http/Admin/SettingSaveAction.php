<?php

declare(strict_types=1);

namespace App\Setting\Http\Admin;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Setting\Command\SaveSetting;
use App\Setting\Command\SaveSettingCommand;
use App\Setting\Domain\SettingNotFoundException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка сохранения настройки
 */
#[IsGranted('ROLE_USER')]
#[Route('/admin/setting/save', methods: ['POST'])]
#[AsController]
final readonly class SettingSaveAction
{
    public function __construct(
        private SaveSetting $saveSetting,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SaveSettingCommand $saveSettingCommand): SuccessResponse
    {
        try {
            $setting = ($this->saveSetting)($saveSettingCommand);

            ($this->flush)();

            $this->logger->info('Настройка сохранена', [
                'id' => $setting->getId(),
                'type' => $setting->getType()->value,
                'value' => $setting->getValue(),
                self::class => __FUNCTION__,
            ]);
        } catch (SettingNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
