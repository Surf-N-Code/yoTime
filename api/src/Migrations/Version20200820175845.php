<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200820175845 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE timer (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, task_id INT DEFAULT NULL, user_id INT NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME DEFAULT NULL, timer_type VARCHAR(255) NOT NULL, INDEX IDX_6AD0DE1A166D1F9C (project_id), INDEX IDX_6AD0DE1A8DB60186 (task_id), INDEX IDX_6AD0DE1AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1A8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE timer');
    }
}
