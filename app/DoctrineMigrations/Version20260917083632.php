<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Customize\Constant\CustomCsvType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Add Favorite Product CSV type (ID: 20) and default display columns
 */
final class Version20260917083632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Favorite Product CSV type and default display columns into mtb_csv_type and dtb_csv';
    }

    public function up(Schema $schema): void
    {
        $csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;

        // ၁။ mtb_csv_type တွင် Custom CSV Type (ID: 20) ထည့်သွင်းခြင်း
        if ($schema->hasTable('mtb_csv_type')) {
            $typeExists = (int) $this->connection->fetchOne(
                "SELECT COUNT(*) FROM mtb_csv_type WHERE id = ?",
                [$csvTypeId]
            );

            if ($typeExists === 0) {
                $this->addSql(
                    "INSERT INTO mtb_csv_type (id, name, sort_no, discriminator_type) VALUES (?, 'お気に入り商品CSV', (SELECT COALESCE(MAX(t.sort_no), 0) + 1 FROM (SELECT sort_no FROM mtb_csv_type) AS t), 'csvtype')",
                    [$csvTypeId]
                );
            }
        }

        // ၂။ dtb_csv တွင် Output Columns များ ထည့်သွင်းခြင်း
        if ($schema->hasTable('dtb_csv')) {
            $csvColsExists = (int) $this->connection->fetchOne(
                "SELECT COUNT(*) FROM dtb_csv WHERE csv_type_id = ?",
                [$csvTypeId]
            );

            if ($csvColsExists === 0) {
                $items = [
                    ['id',             '商品ID',         1],
                    ['name',           '商品名',         2],
                    ['price02_min',    '販売価格(下限)', 3],
                    ['price02_max',    '販売価格(上限)', 4],
                    ['Status',         '公開状態',       5],
                    ['favorite_count', 'お気に入り数',   6],
                ];

                foreach ($items as [$fieldName, $dispName, $sortNo]) {
                    $this->addSql(
                        "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (?, 'Eccube\\\\Entity\\\\Product', ?, NULL, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')",
                        [$csvTypeId, $fieldName, $dispName, $sortNo]
                    );
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        $csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;

        if ($schema->hasTable('dtb_csv')) {
            $this->addSql("DELETE FROM dtb_csv WHERE csv_type_id = ?", [$csvTypeId]);
        }

        if ($schema->hasTable('mtb_csv_type')) {
            $this->addSql("DELETE FROM mtb_csv_type WHERE id = ?", [$csvTypeId]);
        }
    }
}
