<?php

declare(strict_types=1);

namespace App\Setting\Http\Admin;

use App\Setting\Domain\Settings;
use App\User\SignUp\Domain\UserRole;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка списка настроек
 */
#[IsGranted(UserRole::User->value)]
#[Route('/admin/settings', methods: [Request::METHOD_GET])]
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
                type: $setting->getType()->value,
                value: $setting->getValue(),
                isPublic: $setting->isPublic(),
                createdAt: $setting->getCreatedAt(),
                updatedAt: $setting->getUpdatedAt(),
            );
        }
    }
}
