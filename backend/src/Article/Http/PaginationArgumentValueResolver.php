<?php

declare(strict_types=1);

namespace App\Article\Http;

use App\Article\Query\PaginationArticles\PaginationArticlesQuery;
use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\AsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsService]
final class PaginationArgumentValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<PaginationArticlesQuery>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PaginationArticlesQuery::class) {
            return [];
        }

        $page = (int) $request->query->get('page');
        $count = (int) $request->query->get('count');

        if ($page === 0) {
            $page = null;
        }

        if ($count === 0) {
            $count = null;
        }

        try {
            $query = new PaginationArticlesQuery($page, $count);
        } catch (\InvalidArgumentException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        return [$query];
    }
}
