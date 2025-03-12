<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $role
 * @var non-empty-string $method
 * @var non-empty-string $route_path
 * @var non-empty-string $repository_classname
 * @var non-empty-string $action_classname
 * @var non-empty-string $entity_title
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Ручка получения списка <?php echo $entity_title.PHP_EOL; ?>
 */
#[AsController]
#[Route('<?php echo $route_path; ?>', <?php echo $method; ?>)]
#[IsGranted(<?php echo $role; ?>)]
final readonly class <?php echo $action_classname.PHP_EOL; ?>
{
    public function __construct(
        private <?php echo $repository_classname; ?> $repository,
    ) {}

    public function __invoke(): ApiListObjectResponse
    {
        $list = $this->repository->getAll();

        $pagination = new PaginationResponse(
            total: \count($list),
        );

        return new ApiListObjectResponse(
            data: $list,
            pagination: $pagination,
        );
    }
}
