<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230216142154 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Переименовал поля email, confirmToken, isConfirmed для пользователя';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649703DD68B ON user');
        $this->addSql('ALTER TABLE user CHANGE user_email_is_confirmed is_confirmed TINYINT(1) NOT NULL, CHANGE user_email_confirm_token confirm_token_value BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B6244599 ON user (confirm_token_value)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649B6244599 ON user');
        $this->addSql('ALTER TABLE user CHANGE confirm_token_value user_email_confirm_token BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE is_confirmed user_email_is_confirmed TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649703DD68B ON user (user_email_confirm_token)');
    }
}
