<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $test_name
 * @var non-empty-string $create_params_with_variables
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
#[TestDox('Получение информации о <?php echo $entity_classname; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('Получена информация по <?php echo $entity_classname; ?>')]
    public function testSuccess(): void
    {
        $token = User::auth();

        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params_with_variables; ?>, $token);

        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}", token: $token);
        self::assertSuccessResponse($response);

        $response = self::jsonDecode($response->getContent());

        self::assertNotNull($response['id']);
<?php foreach ($entity_fields as $field) { ?>
        self::assertSame($response['<?php echo $field['fieldName']; ?>'], $<?php echo $field['fieldName']; ?>);
<?php } ?>
    }

    #[TestDox('<?php echo $entity_classname; ?> не найден')]
    public function testNotFound(): void
    {
        $token = User::auth();
        <?php echo $entity_classname; ?>::create(<?php echo $create_params; ?>, $token);

        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();
        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}", token: $token);
        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public static function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
