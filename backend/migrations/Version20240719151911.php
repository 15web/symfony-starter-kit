<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Setting\Domain\SettingType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240719151911 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Настройки';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE setting (id UUID NOT NULL, value VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F74B8988CDE5729 ON setting (type)');

        $this->addSql(
            'INSERT INTO setting (id, type, value, is_public, created_at, updated_at)
                    VALUES (gen_random_uuid(), :setting_type, :setting_value, true, now(), now());',
            [
                'setting_type' => SettingType::SITE_NAME->value,
                'setting_value' => 'symfony-starter-kit',
            ],
        );
        $this->addSql(
            'INSERT INTO setting (id, type, value, is_public, created_at, updated_at)
                    VALUES (gen_random_uuid(), :setting_type, :setting_value, false, now(), now());',
            [
                'setting_type' => SettingType::PHONE->value,
                'setting_value' => '71234567890',
            ],
        );
        $this->addSql(
            'INSERT INTO setting (id, type, value, is_public, created_at, updated_at)
                    VALUES (gen_random_uuid(), :setting_type, :setting_value, false, now(), now());',
            [
                'setting_type' => SettingType::EMAIL_SITE->value,
                'setting_value' => 'info@symfonystarterkit.ru',
            ],
        );
        $this->addSql(
            'INSERT INTO setting (id, type, value, is_public, created_at, updated_at)
                    VALUES (gen_random_uuid(), :setting_type, :setting_value, false, now(), now());',
            [
                'setting_type' => SettingType::EMAIL_FROM->value,
                'setting_value' => 'support@symfonystarterkit.ru',
            ],
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE setting');
    }
}
