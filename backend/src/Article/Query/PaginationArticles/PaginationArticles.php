<?php

declare(strict_types=1);

namespace App\Article\Query\PaginationArticles;

use App\Infrastructure\AsService;
use Doctrine\DBAL\Connection;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;

#[AsService]
final class PaginationArticles
{
    public function __construct(
        private readonly Connection $connection,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    public function __invoke(PaginationArticlesQuery $query): PaginationInfo
    {
        $qb = $this->connection->createQueryBuilder()
            ->from('article')
            ->select([
                'title',
                'alias',
            ]);

        $qb->orderBy('title', 'ASC');

        /** @var SlidingPagination<int, array<string,string>> $data */
        $data = $this->paginator->paginate($qb, $query->page, $query->count);

        $items = [];

        foreach ($data->getItems() as $item) {
            $items[] = new Item($item['title'], $item['alias']);
        }

        return new PaginationInfo(
            $items,
            $data->getItemNumberPerPage(),
            $data->getTotalItemCount(),
            $data->getCurrentPageNumber(),
            $data->getPageCount(),
        );
    }
}
