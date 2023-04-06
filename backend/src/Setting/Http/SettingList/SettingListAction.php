<?php

declare(strict_types=1);

namespace App\Setting\Http\SettingList;

use App\Setting\Domain\Settings;
use Generator;
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
    public function __construct(private Settings $settings)
    {
    }

    /**
     * @return Generator<SettingListData>
     */
    public function __invoke(): Generator
    {
        $settings = $this->settings->getAllForPublic();

        foreach ($settings as $setting) {
            yield new SettingListData($setting->getType()->value, $setting->getValue());
        }
    }
}
