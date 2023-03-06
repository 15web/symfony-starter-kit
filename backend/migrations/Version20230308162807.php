<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308162807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавляет таблицу настройки, а также заполняет первоначальными значениями';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE setting (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', value VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_9F74B8988CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO setting (id, type, value, is_public, created_at, updated_at) VALUES (UUID_TO_BIN(UUID()), 'site_name', 'symfony-starter-kit', true, NOW(), NOW());");
        $this->addSql("INSERT INTO setting (id, type, value, is_public, created_at, updated_at) VALUES (UUID_TO_BIN(UUID()), 'phone', '71234567890', true, NOW(), NOW());");
        $this->addSql("INSERT INTO setting (id, type, value, is_public, created_at, updated_at) VALUES (UUID_TO_BIN(UUID()), 'email_site', 'info@symfonystarterkit.ru', true, NOW(), NOW());");
        $this->addSql("INSERT INTO setting (id, type, value, is_public, created_at, updated_at) VALUES (UUID_TO_BIN(UUID()), 'email_from', 'support@symfonystarterkit.ru', false, NOW(), NOW());");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE setting');
    }
}
