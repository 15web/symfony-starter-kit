<?php

namespace Dev\Tests\Rector\OneFlushInClassRector\Fixture;

use App\Infrastructure\Flush;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SomeClass
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function handle(): void
    {
        $this->em->flush();
    }
}

final readonly class AnotherClass
{
    public function __construct(
        private Flush $flush,
    ) {}

    public function handle(): void
    {
        ($this->flush)();
    }
}
?>