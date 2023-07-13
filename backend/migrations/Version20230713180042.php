<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230713180042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id UUID NOT NULL, title VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN article.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN article.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE profile (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(255) NOT NULL, phone_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_profile_user_id ON profile (user_id)');
        $this->addSql('COMMENT ON COLUMN profile.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN profile.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE recovery_token (id UUID NOT NULL, user_id UUID NOT NULL, token UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_recovery_token_user_id ON recovery_token (user_id)');
        $this->addSql('COMMENT ON COLUMN recovery_token.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN recovery_token.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN recovery_token.token IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE seo (id UUID NOT NULL, type VARCHAR(255) NOT NULL, identity VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, keywords TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C71EC308CDE5729 ON seo (type)');
        $this->addSql('COMMENT ON COLUMN seo.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN seo.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN seo.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE setting (id UUID NOT NULL, value VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F74B8988CDE5729 ON setting (type)');
        $this->addSql('COMMENT ON COLUMN setting.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN setting.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN setting.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE task (id UUID NOT NULL, user_id UUID NOT NULL, is_completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, task_name_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_task_user_id ON task (user_id)');
        $this->addSql('COMMENT ON COLUMN task.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.completed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE task_comment (id UUID NOT NULL, task_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, body_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B9578868DB60186 ON task_comment (task_id)');
        $this->addSql('COMMENT ON COLUMN task_comment.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task_comment.task_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task_comment.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task_comment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, user_role VARCHAR(255) NOT NULL, is_confirmed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_email_value VARCHAR(255) NOT NULL, confirm_token_value UUID NOT NULL, user_password_value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B6244599 ON "user" (confirm_token_value)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".confirm_token_value IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_token (id UUID NOT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_user_token_user_id ON user_token (user_id)');
        $this->addSql('COMMENT ON COLUMN user_token.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_token.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_token.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE task_comment ADD CONSTRAINT FK_8B9578868DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE task_comment DROP CONSTRAINT FK_8B9578868DB60186');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE recovery_token');
        $this->addSql('DROP TABLE seo');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_comment');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_token');
    }
}
