<?php

namespace Dev\Tests\Rector\ResolversInAction\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;

#[AsController]
final readonly class SomeClass
{
    public function __invoke(
        Request $request,
        Uuid $id,
    ): array {
        //
    }
}
?>
-----
<?php

namespace Dev\Tests\Rector\ResolversInAction\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;

#[AsController]
final readonly class SomeClass
{
    public function __invoke(
        #[ValueResolver('TODO: добавь резолвер')]
        Request $request,
        #[ValueResolver('TODO: добавь резолвер')]
        Uuid $id,
    ): array {
        //
    }
}
?>
