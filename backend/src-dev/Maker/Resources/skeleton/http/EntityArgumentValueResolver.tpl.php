<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $class_name
 * @var non-empty-string $entity_repository
 * @var non-empty-string $entity_title
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Резолвер для сущности <?php echo $entity_title.PHP_EOL; ?>
 */

final readonly class <?php echo $class_name; ?> implements ValueResolverInterface
{
    public function __construct(
        private <?php echo $entity_repository; ?> $repository,
    ) {}

    /**
     * @return iterable<<?php echo $entity_classname; ?>>
     */
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== <?php echo $entity_classname; ?>::class) {
            return [];
        }

        $id = $request->attributes->getString('id');

        Assert::notEmpty($id);

        try {
            Assert::uuid($id);
        } catch (InvalidArgumentException) {
            throw new ApiNotFoundException(['<?php echo $entity_title; ?> не найден']);
        }

        $entity = $this->repository->findById(
            Uuid::fromString($id),
        );

        if ($entity === null) {
            throw new ApiNotFoundException(['<?php echo $entity_title; ?> не найден']);
        }

        return [$entity];
    }
}
