<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * TODO: зачем этот интерфейс?
 *          зачем в AuthTokenHasher sha256 (не секурно)?
 *              почему не password_hash?
 *       реализации разбросаны по User, хотя иметь здесь один и без интерфейса
*/

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
