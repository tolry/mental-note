<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220816101624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'symfony and bundle upgrade (4.x)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry CHANGE url url VARCHAR(500) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE category category VARCHAR(255) NOT NULL, CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fos_user DROP locked, DROP expired, DROP expires_at, DROP credentials_expired, DROP credentials_expire_at, CHANGE username username VARCHAR(180) NOT NULL, CHANGE username_canonical username_canonical VARCHAR(180) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE email_canonical email_canonical VARCHAR(180) NOT NULL, CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479C05FB297 ON fos_user (confirmation_token)');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8_bin`, CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE visit CHANGE timestamp timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry CHANGE url url VARCHAR(500) DEFAULT NULL, CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('DROP INDEX UNIQ_957A6479C05FB297 ON fos_user');
        $this->addSql('ALTER TABLE fos_user ADD locked TINYINT(1) NOT NULL, ADD expired TINYINT(1) NOT NULL, ADD expires_at DATETIME DEFAULT NULL, ADD credentials_expired TINYINT(1) NOT NULL, ADD credentials_expire_at DATETIME DEFAULT NULL, CHANGE username username VARCHAR(255) NOT NULL, CHANGE username_canonical username_canonical VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE email_canonical email_canonical VARCHAR(255) NOT NULL, CHANGE salt salt VARCHAR(255) NOT NULL, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE visit CHANGE timestamp timestamp DATETIME NOT NULL');
    }
}
