<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20220603114543 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Проверка базы данных';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("SELECT '1';");
    }
}
