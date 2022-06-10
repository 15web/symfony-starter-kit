<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SimpleTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/tasks');

        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
