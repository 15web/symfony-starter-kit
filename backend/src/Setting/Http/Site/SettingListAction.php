<?php

declare(strict_types=1);

namespace App\Setting\Http\Site;

use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\PaginationResponse;
use App\Setting\Domain\Setting;
use App\Setting\Domain\SettingsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка списка настроек
 */
#[Route('/settings', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class SettingListAction
{
    public function __construct(private SettingsRepository $settingsRepository) {}

    public function __invoke(): ApiListObjectResponse
    {
        $settings = $this->settingsRepository->getAllPublic();

        return new ApiListObjectResponse(
            data: $this->buildResponseData($settings),
            pagination: new PaginationResponse(total: \count($settings)),
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
            );
        }
    }
}
