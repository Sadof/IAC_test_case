<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231024073423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADC41EAF32');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADA2A752FA');
        $this->addSql('DROP INDEX IDX_D34A04ADA2A752FA');
        $this->addSql('DROP INDEX IDX_D34A04ADC41EAF32');
        $this->addSql('ALTER TABLE product RENAME COLUMN product_category_id TO product_category_id_id');
        $this->addSql('ALTER TABLE product RENAME COLUMN product_color_id TO product_color_id_id');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC41EAF32 FOREIGN KEY (product_category_id_id) REFERENCES "product_category" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA2A752FA FOREIGN KEY (product_color_id_id) REFERENCES "product_color" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D34A04ADA2A752FA ON product (product_color_id_id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADC41EAF32 ON product (product_category_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "product" DROP CONSTRAINT fk_d34a04adc41eaf32');
        $this->addSql('ALTER TABLE "product" DROP CONSTRAINT fk_d34a04ada2a752fa');
        $this->addSql('DROP INDEX idx_d34a04adc41eaf32');
        $this->addSql('DROP INDEX idx_d34a04ada2a752fa');
        $this->addSql('ALTER TABLE "product" RENAME COLUMN product_category_id_id TO product_category_id');
        $this->addSql('ALTER TABLE "product" RENAME COLUMN product_color_id_id TO product_color_id');
        $this->addSql('ALTER TABLE "product" ADD CONSTRAINT fk_d34a04adc41eaf32 FOREIGN KEY (product_category_id) REFERENCES product_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "product" ADD CONSTRAINT fk_d34a04ada2a752fa FOREIGN KEY (product_color_id) REFERENCES product_color (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d34a04adc41eaf32 ON "product" (product_category_id)');
        $this->addSql('CREATE INDEX idx_d34a04ada2a752fa ON "product" (product_color_id)');
    }
}
