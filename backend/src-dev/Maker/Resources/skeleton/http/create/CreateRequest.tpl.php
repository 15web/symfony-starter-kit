<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $entity_title
 * @var non-empty-string $properties
 * @var array<int, array<string, string>> $entity_fields
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

/**
 * Запрос для создания <?php echo $entity_title.PHP_EOL; ?>
 */
final readonly class Create<?php echo $entity_classname; ?>Request
{
    /**
<?php foreach ($entity_fields as $entity_field) { ?>
<?php if (isset($entity_field->nullable)) {
    continue;
}?>
     * @param non-empty-string $<?php echo $entity_field->propertyName.PHP_EOL; ?>
<?php } ?>
     */
    public function __construct(
<?php echo $properties; ?>
    ) {}
}
