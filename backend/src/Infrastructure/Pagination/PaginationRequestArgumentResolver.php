<?php

declare(strict_types=1);

namespace App\Infrastructure\Pagination;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\AsService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Резолвер для запроса на пагинацию
 */
#[AsService]
final class PaginationRequestArgumentResolver implements ValueResolverInterface
{
    private const QUERY_PAGE_NAME = 'page';
    private const QUERY_PER_PAGE_NAME = 'per-page';

    /**
     * @return iterable<PaginationRequest>
     *
     * @throws ApiBadRequestException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PaginationRequest::class) {
            return [];
        }

        $page = $request->query->getInt(self::QUERY_PAGE_NAME, 1);
        $perPage = $request->query->getInt(self::QUERY_PER_PAGE_NAME, 10);

        try {
            $paginationRequest = new PaginationRequest($page, $perPage);
        } catch (InvalidArgumentException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        return [$paginationRequest];
    }
}
