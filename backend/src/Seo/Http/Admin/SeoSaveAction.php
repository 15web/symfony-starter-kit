<?php

declare(strict_types=1);

namespace App\Seo\Http\Admin;

use App\Infrastructure\ApiRequestValueResolver;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Seo\Command\SaveSeo;
use App\Seo\Command\SaveSeoCommand;
use App\User\SignUp\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка сохранения SEO
 */
#[IsGranted(UserRole::User->value)]
#[Route('/admin/seo/save', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class SeoSaveAction
{
    public function __construct(
        private SaveSeo $saveSeo,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        SaveSeoCommand $saveSeoCommand,
    ): SuccessResponse {
        $seo = ($this->saveSeo)($saveSeoCommand);

        ($this->flush)();

        $this->logger->info('SEO сохранено', [
            'id' => $seo->getId(),
            'type' => $seo->getType()->value,
            self::class => __FUNCTION__,
        ]);

        return new SuccessResponse();
    }
}
