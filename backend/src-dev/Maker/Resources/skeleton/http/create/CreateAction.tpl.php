<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Ручка создания <?php echo $entity_classname."\n"; ?>
 */
#[IsGranted(<?php echo $role; ?>)]
#[Route('<?php echo $route_path; ?>', <?php echo $method; ?>)]
#[AsController]
final class CreateAction
{
    public function __construct(private readonly <?php echo $repository_classname; ?> $repository, private readonly Flush $flush)
    {
    }

    public function __invoke(#[ApiRequest] CreateRequest $createRequest): SuccessResponse
    {
        // TODO: создать сущность
        $entity = new <?php echo $entity_classname; ?>();
        $this->repository->add($entity);
        ($this->flush)();

        return new SuccessResponse();
    }
}
