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
#[TestDox('Получение информации о <?php echo $entity_title; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('Получена информация о <?php echo $entity_title; ?>')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');

        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(
<?php echo $create_params_with_variables; ?>
            token: $token,
        );

        $response = self::request(
            method: Request::METHOD_GET,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            token: $token,
        );

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

    #[TestDox('<?php echo $entity_title; ?> не найден')]
    public function testNotFound(): void
    {
        $token = User::auth('admin@example.test');

        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[TestDox('Некорректный id')]
    public function testInvalidId(): void
    {
        $token = User::auth('admin@example.test');

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s/invalid-id',
            token: $token,
            validateRequestSchema: false,
        );

        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(?string $notValidToken): void
    {
        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            token: $notValidToken,
            validateRequestSchema: false,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Неавторизованный запрос с токеном пользователя')]
    public function testUserAccessDenied(): void
    {
        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();

        $token = User::auth();

        $response = self::request(
            method: Request::METHOD_GET,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            token: $token,
        );

        self::assertAccessDenied($response);
    }
}
