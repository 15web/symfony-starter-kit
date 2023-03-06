<?php

declare(strict_types=1);

namespace App\Setting\Http\Admin;

use App\Setting\Domain\Settings;
use Generator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка списка настроек
 */
#[IsGranted('ROLE_USER')]
#[Route('/admin/settings', methods: ['GET'])]
#[AsController]
final readonly class ListAction
{
    public function __construct(private Settings $settings)
    {
    }

    /**
     * @return Generator<SettingListData>
     */
    public function __invoke(): Generator
    {
        $settings = $this->settings->getAll();

        foreach ($settings as $setting) {
            yield new SettingListData(
                $setting->getType()->value,
                $setting->getValue(),
                $setting->isPublic(),
                $setting->getCreatedAt(),
                $setting->getUpdatedAt(),
            );
        }
    }
}
