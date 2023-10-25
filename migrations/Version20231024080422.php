<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231024080422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_d34a04adc41eaf32');
        $this->addSql('DROP INDEX idx_d34a04adc41eaf32');
        $this->addSql('ALTER TABLE product RENAME COLUMN product_category_id_id TO product_category_id');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADBE6903FD FOREIGN KEY (product_category_id) REFERENCES "product_category" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D34A04ADBE6903FD ON product (product_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "product" DROP CONSTRAINT FK_D34A04ADBE6903FD');
        $this->addSql('DROP INDEX IDX_D34A04ADBE6903FD');
        $this->addSql('ALTER TABLE "product" RENAME COLUMN product_category_id TO product_category_id_id');
        $this->addSql('ALTER TABLE "product" ADD CONSTRAINT fk_d34a04adc41eaf32 FOREIGN KEY (product_category_id_id) REFERENCES product_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d34a04adc41eaf32 ON "product" (product_category_id_id)');
    }
}
