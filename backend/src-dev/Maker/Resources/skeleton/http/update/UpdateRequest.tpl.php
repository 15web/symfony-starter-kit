<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $properties
 * @var array<int, array<string, string>> $entity_fields
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Объект запроса для обновления <?php echo $entity_classname."\n"; ?>
 */
final readonly class UpdateRequest
{
    public function __construct(<?php echo $properties; ?>) {
<?php foreach ($entity_fields as $entity_field) { ?>
<?php if (isset($entity_field['nullable'])) {
    continue;
} ?>
        Assert::notEmpty($<?php echo $entity_field['fieldName']; ?>);
<?php } ?>
    }
}

