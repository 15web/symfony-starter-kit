<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240730133406 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Удаляет ранее созданные токены и добавляет поле hash в user_token';
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
