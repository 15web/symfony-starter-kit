<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Ручка обновления <?php echo $entity_classname."\n"; ?>
 */
#[IsGranted(<?php echo $role; ?>)]
#[Route('<?php echo $route_path; ?>', <?php echo $method; ?>)]
#[AsController]
final class UpdateAction
{
    public function __construct(private readonly Flush $flush)
    {
    }

    public function __invoke(<?php echo $entity_classname; ?> $entity, #[ApiRequest] UpdateRequest $updateRequest): SuccessResponse
    {
        // TODO: обновить сущность
        ($this->flush)();

        return new SuccessResponse();
    }
}
