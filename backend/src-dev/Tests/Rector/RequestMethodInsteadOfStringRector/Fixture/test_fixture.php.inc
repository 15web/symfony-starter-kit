<?php

namespace Dev\Tests\Rector\RequestMethodInsteadOfStringRector\Fixture;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/articles', methods: ['GET'])]
final readonly class GetMethodClass
{
    public function __invoke(): void {}
}

#[Route('/admin/articles', methods: ['POST'])]
final readonly class PostMethodClass
{
    public function __invoke(): void {}
}

#[Route('/admin/articles', methods: ['DELETE'])]
final readonly class DeleteMethodClass
{
    public function __invoke(): void {}
}
?>
-----
<?php

namespace Dev\Tests\Rector\RequestMethodInsteadOfStringRector\Fixture;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/articles', methods: [\Symfony\Component\HttpFoundation\Request::METHOD_GET])]
final readonly class GetMethodClass
{
    public function __invoke(): void {}
}

#[Route('/admin/articles', methods: [\Symfony\Component\HttpFoundation\Request::METHOD_POST])]
final readonly class PostMethodClass
{
    public function __invoke(): void {}
}

#[Route('/admin/articles', methods: [\Symfony\Component\HttpFoundation\Request::METHOD_DELETE])]
final readonly class DeleteMethodClass
{
    public function __invoke(): void {}
}
?>
