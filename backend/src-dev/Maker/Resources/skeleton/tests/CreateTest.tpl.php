<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 */
#[TestDox('Создание <?php echo $entity_classname; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('<?php echo $entity_classname; ?> создан')]
    public function testSuccess(): void
    {
        $token = User::auth();
        $response = <?php echo $entity_classname; ?>::create(<?php echo $create_params_with_variables; ?>, $token);

        self::assertSuccessResponse($response);

        $response = self::jsonDecode($response->getContent());

        self::assertNotNull($response['id']);
<?php foreach ($entity_fields as $field) { ?>
        self::assertSame($response['<?php echo $field['fieldName']; ?>'], $<?php echo $field['fieldName']; ?>);
<?php } ?>
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $response = <?php echo $entity_classname; ?>::create(<?php echo $create_params; ?>, $notValidToken);

        self::assertAccessDenied($response);
    }

    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth();

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request('POST', '/api/<?php echo $entity_classname_small; ?>s/create', $body, token: $token, disableValidateRequestSchema: true);
        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное именование поля' => [['badKey']];
    }
}
