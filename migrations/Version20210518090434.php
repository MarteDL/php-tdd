<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210518090434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room CHANGE only_for_premium_members only_for_premium_members TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE credit credit INT DEFAULT 100 NOT NULL, CHANGE premium_member premium_member TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room CHANGE only_for_premium_members only_for_premium_members TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE credit credit INT NOT NULL, CHANGE premium_member premium_member TINYINT(1) NOT NULL');
    }
}
