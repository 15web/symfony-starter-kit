<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020023033 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Добавляет токен подтверждения почты и подтверждена ли почта';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD user_email_confirm_token BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD user_email_is_confirmed TINYINT(1) NOT NULL');
        $this->addSql('UPDATE user SET user_email_confirm_token = UUID_TO_BIN(UUID())');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649703DD68B ON user (user_email_confirm_token)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649703DD68B ON user');
        $this->addSql('ALTER TABLE user DROP user_email_confirm_token, DROP user_email_is_confirmed');
    }
}
