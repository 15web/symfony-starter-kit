<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Task;

use App\Infrastructure\ApiException\ApiErrorCode;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Task;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * @internal
 */
#[TestDox('Экспорт задач в разные форматы')]
final class ExportTasksTest extends ApiWebTestCase
{
    #[TestDox('Экспорт в формате csv')]
    public function testCsvExport(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        /** @var BinaryFileResponse $response */
        $response = self::request(Request::METHOD_GET, '/api/export/tasks.csv', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $csvEncoder = new CsvEncoder();

        /** @var array<int, array{
         *     id: string,
         *     taskName: string,
         *     isCompleted: string,
         * }> $tasks
         */
        $tasks = $csvEncoder->decode($file->getContent(), CsvEncoder::FORMAT);

        self::assertCount(2, $tasks);
        foreach ($tasks as $task) {
            self::assertNotSame('', $task['id']);
            self::assertNotSame('', $task['taskName']);
            self::assertFalse((bool) $task['isCompleted']);
        }
    }

    #[TestDox('Экспорт в формате xml')]
    public function testXmlExport(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);
        Task::create('Тестовая задача 3', $token);

        /** @var BinaryFileResponse $response */
        $response = self::request(Request::METHOD_GET, '/api/export/tasks.xml', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $xmlEncoder = new XmlEncoder();

        /** @var array<int, array{
         *     id: string,
         *     taskName: string,
         *     createdAt: string,
         *     isCompleted: string,
         * }> $tasks
         */
        $tasks = $xmlEncoder->decode($file->getContent(), XmlEncoder::FORMAT);

        self::assertCount(3, $tasks);
        foreach ($tasks as $task) {
            self::assertNotSame('', $task['id']);
            self::assertNotSame('', $task['taskName']);
            self::assertNotSame('', $task['createdAt']);
            self::assertFalse((bool) $task['isCompleted']);
        }
    }

    #[TestDox('Создано 3 статьи, limit = 2, экспортированы 2 статьи')]
    public function testLimit(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);
        Task::create('Тестовая задача 3', $token);

        /** @var BinaryFileResponse $response */
        $response = self::request(Request::METHOD_GET, '/api/export/tasks.xml?limit=2', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $xmlEncoder = new XmlEncoder();

        /** @var array<int, array{
         *     id: string,
         *     taskName: string,
         *     createdAt: string,
         *     isCompleted: string,
         * }> $tasks
         */
        $tasks = $xmlEncoder->decode($file->getContent(), XmlEncoder::FORMAT);

        self::assertCount(2, $tasks);
    }

    #[TestDox('Создано 3 статьи, limit = 2, offset = 2, экспортирована 1 статья')]
    public function testOffset(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);
        Task::create('Тестовая задача 3', $token);

        /** @var BinaryFileResponse $response */
        $response = self::request(Request::METHOD_GET, '/api/export/tasks.xml?limit=2&offset=2', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $xmlEncoder = new XmlEncoder();

        /** @var array<int, array{
         *     id: string,
         *     taskName: string,
         *     createdAt: string,
         *     isCompleted: string,
         * }> $tasks
         */
        $tasks = $xmlEncoder->decode($file->getContent(), XmlEncoder::FORMAT);

        self::assertCount(1, $tasks);
    }

    #[TestDox('Ошибка при пустом экспорте в формате csv')]
    public function testEmptyCsvExport(): void
    {
        $token = User::auth();

        $response = self::request(Request::METHOD_GET, '/api/export/tasks.csv', token: $token);

        self::assertApiError($response, ApiErrorCode::NotFoundTasksForExport->value);
    }

    #[TestDox('Ошибка при пустом экспорте в формате xml')]
    public function testEmptyXmlExport(): void
    {
        $token = User::auth();

        $response = self::request(Request::METHOD_GET, '/api/export/tasks.xml', token: $token);

        self::assertApiError($response, ApiErrorCode::NotFoundTasksForExport->value);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);

        $response = self::request(Request::METHOD_GET, '/api/export/tasks.xml', token: $notValidToken);

        self::assertAccessDenied($response);

        $response = self::request(Request::METHOD_GET, '/api/export/tasks.csv', token: $notValidToken);

        self::assertAccessDenied($response);
    }

    #[TestDox('Пользователь может экспортировать только свои задачи')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $tokenSecond = User::auth('second@example.com');
        $taskId3 = Task::createAndReturnId($taskName3 = 'Тестовая задача 3', $tokenSecond);
        $taskId4 = Task::createAndReturnId($taskName4 = 'Тестовая задача 4', $tokenSecond);

        /** @var BinaryFileResponse $response */
        $response = self::request(Request::METHOD_GET, '/api/export/tasks.csv', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $csvEncoder = new CsvEncoder();

        /** @var array<int, array{
         *     id: string,
         *     taskName: string,
         *     createdAt: string,
         *     isCompleted: string,
         * }> $tasks
         */
        $tasks = $csvEncoder->decode($file->getContent(), CsvEncoder::FORMAT);

        foreach ($tasks as $task) {
            self::assertNotSame($task['id'], $taskId3);
            self::assertNotSame($task['id'], $taskId4);
            self::assertNotSame($task['taskName'], $taskName3);
            self::assertNotSame($task['taskName'], $taskName4);
        }
    }
}
