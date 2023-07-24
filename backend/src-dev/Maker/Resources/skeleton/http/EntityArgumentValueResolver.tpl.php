<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $class_name
 * @var non-empty-string $entity_repository
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Резолвер для сущности <?php echo $entity_classname."\n"; ?>
 */
#[AsService]
final readonly class <?php echo $class_name; ?> implements ValueResolverInterface
{
    public function __construct(private <?php echo $entity_repository; ?> $repository)
    {
    }

    /**
     * @return iterable<<?php echo $entity_classname; ?>>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== <?php echo $entity_classname; ?>::class) {
            return [];
        }

        /** @var non-empty-string|null $id */
        $id = $request->attributes->get('id');
        if ($id === null) {
            throw new ApiBadRequestException('Укажите id');
        }

        try {
            Assert::uuid($id, 'Укажите валидный id');

            $entity = $this->repository->findById(Uuid::fromString($id));

            if ($entity === null) {
                throw new ApiNotFoundException('Сущность не найдена');
            }
        } catch (InvalidArgumentException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        return [$entity];
    }
}
