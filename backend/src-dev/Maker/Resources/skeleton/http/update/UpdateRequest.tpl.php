<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Объект запроса для обновления <?php echo $entity_classname."\n"; ?>
 */
final class UpdateRequest
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

