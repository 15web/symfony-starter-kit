<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $test_name
 * @var non-empty-string $create_params_with_variables
 * @var non-empty-string $create_params
 * @var non-empty-string $entity_classname_small
 * @var non-empty-string $entity_title
 * @var array<int, array<string, string>> $entity_fields
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 */
#[TestDox('Создание <?php echo $entity_title; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('<?php echo $entity_title; ?> создан')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');
        $response = <?php echo $entity_classname; ?>::create(<?php echo $create_params_with_variables; ?>, $token);

        self::assertSuccessResponse($response);

        /**
         * @var array{
         *     id: string,
         *     createdAt: string,
         *     updatedAt: string|null,
         * } $result
         */
        $result = self::jsonDecode($response->getContent())['data'];

        self::assertNotEmpty($result['id']);
<?php foreach ($entity_fields as $field) { ?>
        self::assertSame($result['<?php echo $field->propertyName; ?>'], $<?php echo $field->propertyName; ?>);
<?php } ?>
        self::assertNotEmpty($result['createdAt']);
        self::assertNull($result['updatedAt']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(?string $notValidToken): void
    {
        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s',
            token: $notValidToken,
            validateRequestSchema: false,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Неавторизованный запрос с токеном пользователя')]
    public function testUserAccessDenied(): void
    {
        $token = User::auth();

        $response = <?php echo $entity_classname; ?>::create(<?php echo $create_params; ?>, $token);

        self::assertAccessDenied($response);
    }

    /**
     * @param array{
     *     field-name: string,
     * } $body
     */
    #[DataProvider('notValidRequestProvider')]
    #[TestDox('Неправильный запрос')]
    public function testBadRequest(array $body): void
    {
        $token = User::auth('admin@example.test');

        $response = self::request(
            method: Request::METHOD_POST,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s',
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
            validateRequestSchema: false,
        );

        self::assertBadRequest($response);
    }

    public static function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [
            [
                'field-name' => '',
            ],
        ];
    }
}
