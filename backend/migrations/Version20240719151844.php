<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240719151844 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Профиль';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE profile (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(255) NOT NULL, phone_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_profile_user_id ON profile (user_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE profile');
    }
}
