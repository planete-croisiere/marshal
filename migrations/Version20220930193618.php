<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220930193618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', active TINYINT(1) DEFAULT 0 NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE oauth2_client (identifier VARCHAR(32) NOT NULL, name VARCHAR(128) NOT NULL, secret VARCHAR(128) DEFAULT NULL, redirect_uris TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_redirect_uri)', grants TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_grant)', scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)', active TINYINT(1) NOT NULL, allow_plain_text_pkce TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE oauth2_user_consent (id INT AUTO_INCREMENT NOT NULL, client_id VARCHAR(32) NOT NULL, user_id INT NOT NULL, created DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', scopes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', ip_address VARCHAR(255) DEFAULT NULL, INDEX IDX_C8F05D0119EB6921 (client_id), INDEX IDX_C8F05D01A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE request_password (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', expire_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_C0E7A6F0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE oauth2_authorization_code (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)', revoked TINYINT(1) NOT NULL, INDEX IDX_509FEF5FC7440455 (client), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE oauth2_refresh_token (identifier CHAR(80) NOT NULL, access_token CHAR(80) DEFAULT NULL, expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', revoked TINYINT(1) NOT NULL, INDEX IDX_4DD90732B6A2DD68 (access_token), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE oauth2_access_token (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)', revoked TINYINT(1) NOT NULL, INDEX IDX_454D9673C7440455 (client), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
            ALTER TABLE oauth2_user_consent ADD CONSTRAINT FK_C8F05D0119EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (identifier);
            ALTER TABLE oauth2_user_consent ADD CONSTRAINT FK_C8F05D01A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id);
            ALTER TABLE request_password ADD CONSTRAINT FK_C0E7A6F0A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id);
            ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE;
            ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL;
            ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE;");
        $this->addSql("INSERT INTO \"user\" (id, email, roles, password, uuid, active) VALUES (1, 'admin@test.com', '[\"ROLE_SUPER_ADMIN\"]', '$2y$13$8Kde6G4pDCflnzDEw8DsyeHiP2h2DyYboHr2kJgLXP7wWgJI0o1Mu', 'da9fb310-0d94-41cb-b74b-a92cedad1614', true);");
    }

    public function down(Schema $schema): void
    {
    }
}
