<?php declare(strict_types=1);

use Symfony\Bundle\MakerBundle\Doctrine\EntityRelation;
use Symfony\Bundle\MakerBundle\Util\ClassSource\Model\ClassProperty;

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
 * @var list<ClassProperty|EntityRelation> $fields
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
        Create<?php echo $entity_classname; ?>Request $request,
    ): ApiObjectResponse {
        // TODO: создать сущность
        $entity = new <?php echo $entity_classname; ?>(
            id: new UuidV7(),
<?php foreach ($fields as $field) { ?>
<?php echo "            {$field->propertyName}: \$request->{$field->propertyName},".PHP_EOL; ?>
<?php } ?>
        );

        $this->repository->add($entity);
        ($this->flush)();

        return ($this->infoAction)($entity);
    }
}
