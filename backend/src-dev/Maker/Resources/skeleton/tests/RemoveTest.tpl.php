<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_classname
 * @var non-empty-string $test_name
 * @var non-empty-string $create_params
 * @var non-empty-string $entity_classname_small
 * @var non-empty-string $entity_title
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 */
#[TestDox('Удаление <?php echo $entity_title; ?>')]
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    #[TestDox('<?php echo $entity_title; ?> удален')]
    public function testSuccess(): void
    {
        $token = User::auth('admin@example.test');

        $<?php echo $entity_classname_small; ?>Id1 = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);
        $<?php echo $entity_classname_small; ?>Id2 = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id1}",
            token: $token,
        );

        self::assertSuccessContentResponse($response);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id1}",
            token: $token,
        );

        self::assertNotFound($response);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id2}",
            token: $token,
        );

        self::assertSuccessResponse($response);
    }

    #[TestDox('<?php echo $entity_title; ?> не найден')]
    public function testNotFound(): void
    {
        $token = User::auth('admin@example.test');

        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();

        $response = self::request(
            method: Request::METHOD_DELETE,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
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
            method: Request::METHOD_DELETE,
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
            method: Request::METHOD_DELETE,
            uri: "/api/admin/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}",
            token: $token,
        );

        self::assertAccessDenied($response);
    }
}
