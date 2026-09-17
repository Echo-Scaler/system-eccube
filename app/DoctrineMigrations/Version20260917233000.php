<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Registering Custom CSV Type (Favorite Product CSV) into mtb_csv_type and dtb_csv
 */
final class Version20260917233000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // ၁။ mtb_csv_type table စစ်ဆေးပြီး Custom CSV Type (ID = 8) ထည့်သွင်းခြင်း
        if ($schema->hasTable('mtb_csv_type')) {
            $typeExists = (int) $this->connection->fetchOne(
                "SELECT COUNT(*) FROM mtb_csv_type WHERE id = 8"
            );
            if ($typeExists === 0) {
                $this->addSql(
                    "INSERT INTO mtb_csv_type (id, name, sort_no, discriminator_type) VALUES (8, 'お気に入り商品 CSV', 8, 'csvtype')"
                );
            }
        }

        // ၂။ dtb_csv table စစ်ဆေးပြီး CSV Output Columns များ ထည့်သွင်းခြင်း
        if ($schema->hasTable('dtb_csv')) {
            $csvColsExists = (int) $this->connection->fetchOne(
                "SELECT COUNT(*) FROM dtb_csv WHERE csv_type_id = 8"
            );
            if ($csvColsExists === 0) {
                // Product ID
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'id', NULL, '商品ID', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
                // Product Code
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'code_min', NULL, '商品コード', 2, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
                // Product Name
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'name', NULL, '商品名', 3, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
                // Price
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'price02_inc_tax_min', NULL, '販売価格(税込)', 4, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
                // Favorite Count
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'favorite_count', NULL, 'お気に入り数', 5, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
                // Stock (Disabled by default)
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'stock_min', NULL, '在庫数', 6, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
                // Create Date (Disabled by default)
                $this->addSql(
                    "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (8, 'Eccube\\\\Entity\\\\Product', 'create_date', NULL, '登録日', 7, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')"
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('dtb_csv')) {
            $this->addSql("DELETE FROM dtb_csv WHERE csv_type_id = 8");
        }

        if ($schema->hasTable('mtb_csv_type')) {
            $this->addSql("DELETE FROM mtb_csv_type WHERE id = 8");
        }
    }
}
