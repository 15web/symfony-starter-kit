<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Override;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * Array кэш адаптер без сброса кэша между запросами, для тестирования
 */
#[When('test')]
final class KeepCacheArrayAdapter extends ArrayAdapter
{
    #[Override]
    public function reset(): void {}
}
