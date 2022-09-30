<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220930195353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD first_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD last_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD phone_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE "user" SET first_name=\'\' WHERE first_name IS NULL;');
        $this->addSql('UPDATE "user" SET last_name=\'\' WHERE last_name IS NULL;');
        $this->addSql('ALTER TABLE "user" ALTER first_name SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER last_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP first_name');
        $this->addSql('ALTER TABLE "user" DROP last_name');
        $this->addSql('ALTER TABLE "user" DROP phone_number');
    }
}
