<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729103536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Модель задачи // Объеденить completedAt и isCompleted в один Value Object';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task CHANGE is_completed task_completed_is_completed TINYINT(1) NOT NULL, CHANGE completed_at task_completed_completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task CHANGE task_completed_is_completed is_completed TINYINT(1) NOT NULL, CHANGE task_completed_completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
