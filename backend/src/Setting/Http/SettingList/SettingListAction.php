<?php

declare(strict_types=1);

namespace App\Setting\Http\SettingList;

use App\Setting\Domain\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ручка списка настроек
 */
#[Route('/settings', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class SettingListAction
{
    public function __construct(private Settings $settings) {}

    /**
     * @return iterable<SettingListData>
     */
    public function __invoke(): iterable
    {
        $settings = $this->settings->getAllPublic();

        foreach ($settings as $setting) {
            yield new SettingListData(
                type: $setting->getType()->value,
                value: $setting->getValue(),
            );
        }
    }
}
