<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $role
 * @var non-empty-string $method
 * @var non-empty-string $route_path
 * @var non-empty-string $action_classname
 * @var non-empty-string $entity_title
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Ручка обновления <?php echo $entity_title.PHP_EOL; ?>
 */
#[AsController]
#[Route('<?php echo $route_path; ?>', <?php echo $method; ?>)]
#[IsGranted(<?php echo $role; ?>)]
final readonly class <?php echo $action_classname.PHP_EOL; ?>
{
    public function __construct(
        private Flush $flush,
        private Get<?php echo $entity_classname; ?>Action $infoAction,
    ) {}

    public function __invoke(
        #[ValueResolver(<?php echo $entity_classname; ?>ArgumentValueResolver::class)]
        <?php echo $entity_classname; ?> $entity,
        #[ValueResolver(ApiRequestValueResolver::class)]
        Update<?php echo $entity_classname; ?>Request $updateRequest,
    ): ApiObjectResponse {
        // TODO: обновить сущность
        ($this->flush)();

        return ($this->infoAction)($entity);
    }
}
