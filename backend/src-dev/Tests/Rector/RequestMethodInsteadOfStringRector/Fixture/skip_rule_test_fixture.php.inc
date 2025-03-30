<?php

namespace Dev\Tests\Rector\RequestMethodInsteadOfStringRector\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/articles', methods: [Request::METHOD_POST])]
final readonly class SomeClass
{
    public function __invoke(): void
    {
        //
    }
}
?>