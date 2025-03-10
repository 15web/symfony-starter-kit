<?php

declare(strict_types=1);

namespace App\Seo\Http\Admin;

use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\Seo\Command\SaveSeo;
use App\Seo\Command\SaveSeoCommand;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка сохранения SEO
 */
#[IsGranted(UserRole::Admin)]
#[Route('/admin/seo', methods: [Request::METHOD_POST])]
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
    ): ApiObjectResponse {
        $seo = ($this->saveSeo)($saveSeoCommand);

        ($this->flush)();

        $this->logger->info('SEO сохранено', [
            'id' => $seo->getId(),
            'type' => $seo->getType()->value,
            self::class => __FUNCTION__,
        ]);

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
