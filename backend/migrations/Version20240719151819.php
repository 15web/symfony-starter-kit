<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240719151819 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Восстановление пароля';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recovery_token (id UUID NOT NULL, user_id UUID NOT NULL, token UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_recovery_token_user_id ON recovery_token (user_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE recovery_token');
    }
}
