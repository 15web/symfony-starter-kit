<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240719151955 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Задачи';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE task (id UUID NOT NULL, user_id UUID NOT NULL, is_completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, task_name_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_task_user_id ON task (user_id)');
        $this->addSql('CREATE TABLE task_comment (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, body_value VARCHAR(255) NOT NULL, task_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B9578868DB60186 ON task_comment (task_id)');
        $this->addSql('ALTER TABLE task_comment ADD CONSTRAINT FK_8B9578868DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task_comment DROP CONSTRAINT FK_8B9578868DB60186');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_comment');
    }
}
