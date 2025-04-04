<?php

namespace Dev\Tests\Rector\OneFlushInClassRector\Fixture;

use App\Infrastructure\Flush;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SomeClass
{
    public function __construct(
        private EntityManagerInterface $em,
        private Flush $flush,
    ) {}

    public function handle(): void
    {
        $this->em->flush();
        ($this->flush)();
    }

    public function anotherOne(): void
    {
        $this->em->flush();
    }
}
?>
-----
<?php

namespace Dev\Tests\Rector\OneFlushInClassRector\Fixture;

use App\Infrastructure\Flush;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SomeClass
{
    public function __construct(
        private EntityManagerInterface $em,
        private Flush $flush,
    ) {}

    public function handle(): void
    {
        $this->em->flush();
    }

    public function anotherOne(): void
    {
    }
}
?>
