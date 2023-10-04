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
    private const QUERY_LIMIT_NAME = 'limit';
    private const QUERY_OFFSET_NAME = 'offset';

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

        $offset = $request->query->getInt(self::QUERY_OFFSET_NAME);
        $limit = $request->query->getInt(self::QUERY_LIMIT_NAME, 10);

        try {
            $paginationRequest = new PaginationRequest($offset, $limit);
        } catch (InvalidArgumentException $exception) {
            throw new ApiBadRequestException([$exception->getMessage()]);
        }

        return [$paginationRequest];
    }
}
