<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240315151448 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Добавляет поле hash в user_token и удаляет ранее созданные токены';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('TRUNCATE TABLE user_token');
        $this->addSql('ALTER TABLE user_token ADD hash VARCHAR(255) NOT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_token DROP hash');
    }
}
