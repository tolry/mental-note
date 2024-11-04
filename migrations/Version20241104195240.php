<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104195240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_957A647992FC23A8 ON fos_user');
        $this->addSql('DROP INDEX UNIQ_957A6479A0D96FBF ON fos_user');
        $this->addSql('DROP INDEX UNIQ_957A6479C05FB297 ON fos_user');
        $this->addSql('ALTER TABLE fos_user DROP username_canonical, DROP email_canonical, DROP salt, DROP confirmation_token, DROP password_requested_at, CHANGE username username VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE last_login last_login DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8_bin`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_bin`');
        $this->addSql('ALTER TABLE fos_user ADD username_canonical VARCHAR(180) NOT NULL, ADD email_canonical VARCHAR(180) NOT NULL, ADD salt VARCHAR(255) DEFAULT NULL, ADD confirmation_token VARCHAR(180) DEFAULT NULL, ADD password_requested_at DATETIME DEFAULT NULL, CHANGE username username VARCHAR(180) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE last_login last_login DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A647992FC23A8 ON fos_user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479A0D96FBF ON fos_user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479C05FB297 ON fos_user (confirmation_token)');
    }
}
