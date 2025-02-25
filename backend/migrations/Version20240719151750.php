<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240719151750 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Пользователь и токен авторизации';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, user_role VARCHAR(255) NOT NULL, is_confirmed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_email_value VARCHAR(255) NOT NULL, confirm_token_value UUID NOT NULL, user_password_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B6244599 ON "user" (confirm_token_value)');
        $this->addSql('CREATE TABLE user_token (id UUID NOT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_user_token_user_id ON user_token (user_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_token');
    }
}
