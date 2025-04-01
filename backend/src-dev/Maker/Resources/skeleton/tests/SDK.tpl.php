<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $create_params
 * @var non-empty-string $entity_classname_small
 * @var array<int, array<string, string>> $entity_fields
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 */
final class <?php echo $entity_classname; ?> extends ApiWebTestCase
{
    public static function createAndReturnId(
<?php echo $create_params; ?>
        string $token,
    ): string {
        $response = self::create(
<?php foreach ($entity_fields as $field) { ?>
            <?php echo $field->propertyName; ?>: $<?php echo $field->propertyName; ?>,
<?php } ?>
            token: $token,
        );

        /** @var array{data: array{id: string}} $entity */
        $entity = self::jsonDecode($response->getContent());

        return $entity['data']['id'];
    }

    public static function create(
<?php echo $create_params; ?>
        string $token,
    ): Response {
        $body = [
<?php foreach ($entity_fields as $field) { ?>
            '<?php echo $field->propertyName; ?>' => $<?php echo $field->propertyName; ?>,
<?php } ?>
        ];

        return self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
        );
    }
}
