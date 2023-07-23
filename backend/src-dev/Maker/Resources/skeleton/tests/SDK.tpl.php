<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 */
final class <?php echo $entity_classname; ?> extends ApiWebTestCase
{
    public static function createAndReturnId(<?php echo $create_params; ?>, string $token): string
    {
        $body = [];
<?php foreach ($entity_fields as $field) { ?>
        $body['<?php echo $field['fieldName']; ?>'] = $<?php echo $field['fieldName']; ?>;
<?php } ?>
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', '/api/<?php echo $entity_classname_small; ?>s/create', $body, token: $token);

        $entity = self::jsonDecode($response->getContent());

        return $entity['id'];
    }

    public static function create(<?php echo $create_params; ?>, string $token): Response
    {
        $body = [];
<?php foreach ($entity_fields as $field) { ?>
        $body['<?php echo $field['fieldName']; ?>'] = $<?php echo $field['fieldName']; ?>;
<?php } ?>
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        return self::request('POST', '/api/<?php echo $entity_classname_small; ?>s/create', $body, token: $token);
    }
}
