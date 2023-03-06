<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Ручка удаления <?php echo $entity_classname."\n"; ?>
 */
#[IsGranted('ROLE_USER')]
#[Route('<?php echo $route_path; ?>', <?php echo $method; ?>)]
#[AsController]
final class RemoveAction
{
    public function __construct(private readonly <?php echo $repository_classname; ?> $repository, private readonly Flush $flush)
    {
    }

    public function __invoke(<?php echo $entity_classname; ?> $entity): SuccessResponse
    {
        $this->repository->remove($entity);
        ($this->flush)();

        return new SuccessResponse();
    }
}
