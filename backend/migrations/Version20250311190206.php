<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250311190206 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Добавление тестовых пользователй';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO "user" (id, user_role, is_confirmed, created_at, user_email_value, user_password_value) VALUES (gen_random_uuid(), \'ROLE_USER\', true, now(), \'user@example.test\', \'$2y$08$CxUXg6YoeRyhAKqEXS5tl.H95Y78AKgji/8jEkRrdtdHkwSID0YeC\')');
        $this->addSql('INSERT INTO "user" (id, user_role, is_confirmed, created_at, user_email_value, user_password_value) VALUES (gen_random_uuid(), \'ROLE_ADMIN\', true, now(), \'admin@example.test\', \'$2y$08$CxUXg6YoeRyhAKqEXS5tl.H95Y78AKgji/8jEkRrdtdHkwSID0YeC\')');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM "user" WHERE user_email_value IN (\'user@example.test\', \'admin@example.test\')');
    }
}
