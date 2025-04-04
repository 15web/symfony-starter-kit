<?php

namespace Dev\Tests\Rector\ResolversInAction\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Uid\Uuid;

#[AsController]
final readonly class SomeClass
{
    public function __invoke(
        #[ValueResolver(RequestValueResolver::class)]
        Request $request,
        #[ValueResolver(UidValueResolver::class)]
        Uuid $id,
    ): array {
        //
    }
}

final readonly class NotControllerClass
{
    public function __invoke(
        Request $request,
        Uuid $id,
    ): array {
        //
    }
}

#[AsController]
final readonly class NotInvokableClass
{
    public function handle(
        Request $request,
        Uuid $id,
    ): array {
        //
    }
}
?>