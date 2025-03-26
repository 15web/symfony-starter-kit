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
 * Ручка создания <?php echo $entity_title.PHP_EOL; ?>
 */
#[AsController]
#[Route('<?php echo $route_path; ?>', <?php echo $method; ?>)]
#[IsGranted(<?php echo $role; ?>)]
final readonly class <?php echo $action_classname.PHP_EOL; ?>
{
    public function __construct(
        private <?php echo $repository_classname; ?> $repository,
        private Flush $flush,
        private Get<?php echo $entity_classname; ?>Action $infoAction,
    ) {}

    public function __invoke(
        #[ValueResolver(ApiRequestValueResolver::class)]
        Create<?php echo $entity_classname; ?>Request $createRequest,
    ): ApiObjectResponse {
        // TODO: создать сущность
        $entity = new <?php echo $entity_classname; ?>(
            id: new UuidV7(),
        );

        $this->repository->add($entity);
        ($this->flush)();

        return ($this->infoAction)($entity);
    }
}
