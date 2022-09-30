<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220930193618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE oauth2_user_consent_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE request_password_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 2');
        $this->addSql('CREATE TABLE oauth2_access_token (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('CREATE INDEX IDX_454D9673C7440455 ON oauth2_access_token (client)');
        $this->addSql('COMMENT ON COLUMN oauth2_access_token.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_access_token.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE oauth2_authorization_code (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('CREATE INDEX IDX_509FEF5FC7440455 ON oauth2_authorization_code (client)');
        $this->addSql('COMMENT ON COLUMN oauth2_authorization_code.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_authorization_code.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE oauth2_client (identifier VARCHAR(32) NOT NULL, name VARCHAR(128) NOT NULL, secret VARCHAR(128) DEFAULT NULL, redirect_uris TEXT DEFAULT NULL, grants TEXT DEFAULT NULL, scopes TEXT DEFAULT NULL, active BOOLEAN NOT NULL, allow_plain_text_pkce BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('COMMENT ON COLUMN oauth2_client.redirect_uris IS \'(DC2Type:oauth2_redirect_uri)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_client.grants IS \'(DC2Type:oauth2_grant)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_client.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE oauth2_refresh_token (identifier CHAR(80) NOT NULL, access_token CHAR(80) DEFAULT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('CREATE INDEX IDX_4DD90732B6A2DD68 ON oauth2_refresh_token (access_token)');
        $this->addSql('COMMENT ON COLUMN oauth2_refresh_token.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE oauth2_user_consent (id INT NOT NULL, client_id VARCHAR(32) NOT NULL, user_id INT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, scopes TEXT DEFAULT NULL, ip_address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C8F05D0119EB6921 ON oauth2_user_consent (client_id)');
        $this->addSql('CREATE INDEX IDX_C8F05D01A76ED395 ON oauth2_user_consent (user_id)');
        $this->addSql('COMMENT ON COLUMN oauth2_user_consent.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_user_consent.expires IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_user_consent.scopes IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE request_password (id INT NOT NULL, user_id INT NOT NULL, token UUID NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C0E7A6F0A76ED395 ON request_password (user_id)');
        $this->addSql('COMMENT ON COLUMN request_password.token IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN request_password.expire_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, uuid UUID NOT NULL, active BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D17F50A6 ON "user" (uuid)');
        $this->addSql('COMMENT ON COLUMN "user".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_user_consent ADD CONSTRAINT FK_C8F05D0119EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (identifier) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_user_consent ADD CONSTRAINT FK_C8F05D01A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE request_password ADD CONSTRAINT FK_C0E7A6F0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql("INSERT INTO \"user\" (id, email, roles, password, uuid, active) VALUES (1, 'admin@test.com', '[\"ROLE_SUPER_ADMIN\"]', '$2y$13$8Kde6G4pDCflnzDEw8DsyeHiP2h2DyYboHr2kJgLXP7wWgJI0o1Mu', 'da9fb310-0d94-41cb-b74b-a92cedad1614', true);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE oauth2_user_consent_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE request_password_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE oauth2_access_token DROP CONSTRAINT FK_454D9673C7440455');
        $this->addSql('ALTER TABLE oauth2_authorization_code DROP CONSTRAINT FK_509FEF5FC7440455');
        $this->addSql('ALTER TABLE oauth2_refresh_token DROP CONSTRAINT FK_4DD90732B6A2DD68');
        $this->addSql('ALTER TABLE oauth2_user_consent DROP CONSTRAINT FK_C8F05D0119EB6921');
        $this->addSql('ALTER TABLE oauth2_user_consent DROP CONSTRAINT FK_C8F05D01A76ED395');
        $this->addSql('ALTER TABLE request_password DROP CONSTRAINT FK_C0E7A6F0A76ED395');
        $this->addSql('DROP TABLE oauth2_access_token');
        $this->addSql('DROP TABLE oauth2_authorization_code');
        $this->addSql('DROP TABLE oauth2_client');
        $this->addSql('DROP TABLE oauth2_refresh_token');
        $this->addSql('DROP TABLE oauth2_user_consent');
        $this->addSql('DROP TABLE request_password');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
