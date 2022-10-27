<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\DataFixtures\UserFixtures;
use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

final class ExportTasksTest extends ApiWebTestCase
{
    public function testCsvExport(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        /** @var BinaryFileResponse $response */
        $response = self::request('GET', '/api/export/tasks.csv', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $csvEncoder = new CsvEncoder();

        $tasks = $csvEncoder->decode($file->getContent(), CsvEncoder::FORMAT);

        self::assertCount(2, $tasks);
        foreach ($tasks as $task) {
            self::assertNotSame('', $task['id']);
            self::assertNotSame('', $task['taskName']);
            self::assertFalse((bool) $task['isCompleted']);
        }
    }

    public function testXmlExport(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);
        Task::create('Тестовая задача 3', $token);

        /** @var BinaryFileResponse $response */
        $response = self::request('GET', '/api/export/tasks.xml', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $xmlEncoder = new XmlEncoder();

        $tasks = $xmlEncoder->decode($file->getContent(), XmlEncoder::FORMAT);

        self::assertCount(3, $tasks);
        foreach ($tasks as $task) {
            self::assertNotSame('', $task['id']);
            self::assertNotSame('', $task['taskName']);
            self::assertNotSame('', $task['createdAt']);
            self::assertFalse((bool) $task['isCompleted']);
        }
    }

    public function testEmptyCsvExport(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        /** @var BinaryFileResponse $response */
        $response = self::request('GET', '/api/export/tasks.csv', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $csvEncoder = new CsvEncoder();

        $tasks = $csvEncoder->decode($file->getContent(), CsvEncoder::FORMAT);

        self::assertSame("\n\n", $file->getContent()); // пустой файл содержит "\n\n"
        self::assertSame([['' => null]], $tasks); // [['' => null]] такой формат выдается при decode пустого файла
    }

    public function testEmptyXmlExport(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;

        /** @var BinaryFileResponse $response */
        $response = self::request('GET', '/api/export/tasks.xml', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $xmlEncoder = new XmlEncoder();

        $tasks = $xmlEncoder->decode($file->getContent(), XmlEncoder::FORMAT);

        self::assertEmpty($tasks);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        Task::create('Тестовая задача 1', $token);

        $response = self::request('GET', '/api/export/tasks.xml', token: $notValidToken);

        self::assertAccessDenied($response);

        $response = self::request('GET', '/api/export/tasks.csv', token: $notValidToken);

        self::assertAccessDenied($response);
    }

    public function testNoAccessAnotherUser(): void
    {
        $token = UserFixtures::FIST_USER_TOKEN;
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $this->tearDown();
        $tokenSecond = UserFixtures::SECOND_USER_TOKEN;
        $taskId3 = Task::createAndReturnId($taskName3 = 'Тестовая задача 3', $tokenSecond);
        $taskId4 = Task::createAndReturnId($taskName4 = 'Тестовая задача 4', $tokenSecond);

        /** @var BinaryFileResponse $response */
        $response = self::request('GET', '/api/export/tasks.csv', token: $token);
        self::assertSuccessResponse($response);

        $file = $response->getFile();
        $csvEncoder = new CsvEncoder();

        $tasks = $csvEncoder->decode($file->getContent(), CsvEncoder::FORMAT);

        foreach ($tasks as $task) {
            self::assertNotSame($task['id'], $taskId3);
            self::assertNotSame($task['id'], $taskId4);
            self::assertNotSame($task['taskName'], $taskName3);
            self::assertNotSame($task['taskName'], $taskName4);
        }
    }
}
