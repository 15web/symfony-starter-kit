<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220603114543 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Проверка mysql';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('SELECT 1;');
    }
}
