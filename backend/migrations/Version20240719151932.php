<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240719151932 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Статьи';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE article (id UUID NOT NULL, title VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE article');
    }
}
