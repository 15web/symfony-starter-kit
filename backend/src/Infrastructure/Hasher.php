<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Интерфейс сервиса хеширования
 */
#[AutoconfigureTag]
interface Hasher
{
    /**
     * @param non-empty-string $data
     *
     * @return non-empty-string
     */
    public function hash(string $data): string;

    /**
     * @param non-empty-string $data
     * @param non-empty-string $hash
     */
    public function verify(string $data, string $hash): bool;
}
