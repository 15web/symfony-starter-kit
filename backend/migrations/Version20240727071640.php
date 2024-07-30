<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240727071640 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'ConfirmToken обнуляется после подтверждения';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER COLUMN confirm_token_value DROP NOT NULL;');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER COLUMN confirm_token_value SET NOT NULL');
    }
}
