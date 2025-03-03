<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $test_name
 * @var non-empty-string $create_params
 * @var non-empty-string $entity_classname_small
 * @var array<string, float|int|string|null> $data_for_update
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
#[TestDox('Получение списка <?php echo $entity_title; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('Список успешно получен')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');

        $<?php echo $entity_classname_small; ?>Id1 = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);
        $<?php echo $entity_classname_small; ?>Id2 = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s',
            token: $token,
        );

        self::assertSuccessResponse($response);

        /**
         * @var array{
         *     data: list<array{
         *         id: string,
         *         createdAt: string,
         *         updatedAt: string|null,
         *     }>,
         *     pagination: array{total: int}
         * } $result
         */
        $result = self::jsonDecode($response->getContent());

        self::assertCount(2, $result['data']);

        self::assertSame($result['data'][0]['id'], $<?php echo $entity_classname_small; ?>Id1);
        self::assertSame($result['data'][1]['id'], $<?php echo $entity_classname_small; ?>Id2);
        self::assertSame(2, $result['pagination']['total']);
    }

    #[TestDox('Получение пустого списка')]
    public function testEmptyList(): void
    {
        $token = User::auth('admin@example.test');

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s',
            token: $token,
        );

        self::assertSuccessResponse($response);

        //  проверяем что в data пустой массив, а не пустой объект {}
        self::assertSame('{"data":[],"pagination":{"total":0},"meta":null,"status":"success"}', $response->getContent());

        /**
         * @var array{
         *     data: array<array-key, mixed>,
         *     pagination: array{total: int}
         * } $result
         */
        $result = self::jsonDecode($response->getContent());

        self::assertSame([], $result['data']);
        self::assertSame(0, $result['pagination']['total']);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(?string $notValidToken): void
    {
        $response = self::request(
            method: Request::METHOD_GET,
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

        $response = self::request(
            method: Request::METHOD_GET,
            uri: '/api/admin/<?php echo $entity_classname_small; ?>s',
            token: $token,
        );

        self::assertAccessDenied($response);
    }
}
