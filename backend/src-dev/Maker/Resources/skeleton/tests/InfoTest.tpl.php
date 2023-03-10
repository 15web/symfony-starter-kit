<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 *
 * @testdox Получение информации о <?php echo $entity_classname."\n"; ?>
 */
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    /**
     * @testdox Получена информация по <?php echo $entity_classname."\n"; ?>
     */
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

    /**
     * @testdox <?php echo $entity_classname; ?> не найден
     */
    public function testNotFound(): void
    {
        $token = User::auth();
        <?php echo $entity_classname; ?>::create(<?php echo $create_params; ?>, $token);

        $<?php echo $entity_classname_small; ?>Id = (string) Uuid::v4();
        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request('GET', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
