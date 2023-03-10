<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @internal
 *
 * @testdox Создание <?php echo $entity_classname."\n"; ?>
 */
final class <?php echo $test_name; ?> extends ApiWebTestCase
{
    /**
     * @testdox <?php echo $entity_classname; ?> обновлен
     */
    public function testSuccess(): void
    {
        $token = User::auth();
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $body = [];
<?php foreach ($data_for_update as $name => $value) { ?>
        $body['<?php echo $name; ?>'] = <?php echo $value; ?>;
<?php } ?>
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}/update", $body, token: $token);

        self::assertSuccessResponse($response);

        $response = self::jsonDecode($response->getContent());

<?php foreach ($entity_fields as $field) { ?>
        self::assertSame($response['<?php echo $field['fieldName']; ?>'], $body['<?php echo $field['fieldName']; ?>']);
<?php } ?>
    }

    /**
     * @testdox <?php echo $entity_classname; ?> не найден
     */
    public function testNotFound(): void
    {
        $token = User::auth();
        <?php echo $entity_classname; ?>::create(<?php echo $create_params; ?>, $token);

        $body = [];
<?php foreach ($data_for_update as $name => $value) { ?>
        $body['<?php echo $name; ?>'] = <?php echo $value; ?>;
<?php } ?>
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $<?php echo $entity_classname_small; ?>Id = (string) new UuidV7();
        $response = self::request('POST', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}/update", $body, token: $token);
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

        $body = [];
<?php foreach ($data_for_update as $name => $value) { ?>
        $body['<?php echo $name; ?>'] = <?php echo $value; ?>;
<?php } ?>
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/<?php echo $entity_classname_small; ?>s/{$<?php echo $entity_classname_small; ?>Id}/update", $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    /**
     * @dataProvider notValidRequestProvider
     *
     * @testdox Неправильный запрос
     */
    public function testBadRequest(array $body): void
    {
        $token = User::auth();
        $<?php echo $entity_classname_small; ?>Id = <?php echo $entity_classname; ?>::createAndReturnId(<?php echo $create_params; ?>, $token);

        $body = json_encode($body, JSON_THROW_ON_ERROR);
        $response = self::request(
            'POST',
            "/api/articles/{$<?php echo $entity_classname_small; ?>Id}/update",
            $body,
            token: $token,
            disableValidateRequestSchema: true
        );

        self::assertBadRequest($response);
    }

    public function notValidRequestProvider(): Iterator
    {
        yield 'пустой запрос' => [['']];

        yield 'неверное имя поля в запросе' => [['badKey']];
    }
}
