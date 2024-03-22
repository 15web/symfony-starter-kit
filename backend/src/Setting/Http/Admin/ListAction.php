<?php

declare(strict_types=1);

namespace App\Setting\Http\Admin;

use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationResponse;
use App\Setting\Domain\Setting;
use App\Setting\Domain\SettingsRepository;
use App\User\User\Domain\UserRole;
use App\User\User\Http\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка списка настроек
 */
#[IsGranted(UserRole::User)]
#[Route('/admin/settings', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ListAction
{
    public function __construct(private SettingsRepository $settingsRepository) {}

    public function __invoke(): ApiListObjectResponse
    {
        $settings = $this->settingsRepository->getAll();

        return new ApiListObjectResponse(
            data: $this->buildResponseData($settings),
            pagination: new PaginationResponse(\count($settings))
        );
    }

    /**
     * @param list<Setting> $settings
     *
     * @return iterable<SettingListData>
     */
    private function buildResponseData(array $settings): iterable
    {
        foreach ($settings as $setting) {
            yield new SettingListData(
                type: $setting->getType()->value,
                value: $setting->getValue(),
                isPublic: $setting->isPublic(),
                createdAt: $setting->getCreatedAt(),
                updatedAt: $setting->getUpdatedAt(),
            );
        }
    }
}
