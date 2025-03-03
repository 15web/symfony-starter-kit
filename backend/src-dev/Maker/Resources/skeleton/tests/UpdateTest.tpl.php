<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $test_name
 * @var non-empty-string $create_params
 * @var non-empty-string $entity_classname_small
 * @var array<string, float|int|string|null> $data_for_update
 * @var array<int, array<string, string>> $entity_fields
 * @var non-empty-string $entity_title
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 */
#[TestDox('Обновление <?php echo $entity_title; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('<?php echo $entity_title; ?> обновлен')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $body = [
<?php foreach ($data_for_update as $name => $value) { ?>
            '<?php echo $name; ?>' => <?php echo $value; ?>,
<?php } ?>
        ];

        $response = self::request(
            method: Request::METHOD_PUT,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            body: json_encode($body, JSON_THROW_ON_ERROR),
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

<?php foreach ($entity_fields as $field) { ?>
        self::assertSame($result['<?php echo $field->propertyName; ?>'], $body['<?php echo $field->propertyName; ?>']);
<?php } ?>
    }

    #[TestDox('<?php echo $entity_title; ?> не найден')]
    public function testNotFound(): void
    {
        $token = User::auth('admin@example.test');

        $body = [
<?php foreach ($data_for_update as $name => $value) { ?>
            '<?php echo $name; ?>' => <?php echo $value; ?>,
<?php } ?>
        ];

        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();

        $response = self::request(
            method: Request::METHOD_PUT,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            body: json_encode($body, JSON_THROW_ON_ERROR),
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(?string $notValidToken): void
    {
        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();

        $response = self::request(
            method: Request::METHOD_PUT,
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
            method: Request::METHOD_PUT,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            token: $token,
            validateRequestSchema: false,
        );

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
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request(
            method: Request::METHOD_PUT,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
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
