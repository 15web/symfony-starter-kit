<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Объект запроса для обновления <?php echo $entity_classname."\n"; ?>
 */
final class UpdateRequest implements ApiRequest
{
    public function __construct(
    ) {
    }
}

