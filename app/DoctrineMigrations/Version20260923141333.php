<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Customize\Constant\CustomCsvType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add Favorite Products CSV Type (ID: 20) and default columns to mtb_csv_type and dtb_csv
 */
final class Version20260923141333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Favorite Products CSV Type (ID: 20) to mtb_csv_type and default columns to dtb_csv';
    }

    public function up(Schema $schema): void
    {
        $csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;

        // 1. mtb_csv_type တွင် Custom CSV Type (ID: 20) ထည့်သွင်းခြင်း
        if ($schema->hasTable('mtb_csv_type')) {
            $typeExists = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM mtb_csv_type WHERE id = ?',
                [$csvTypeId]
            );

            if ($typeExists === 0) {
                $this->addSql(
                    "INSERT INTO mtb_csv_type (id, name, sort_no, discriminator_type) VALUES (?, 'お気に入り商品CSV', 20, 'csvtype')",
                    [$csvTypeId]
                );
            }
        }

        // 2. dtb_csv တွင် Favorite Products အတွက် Default Columns များ ထည့်သွင်းခြင်း
        if ($schema->hasTable('dtb_csv')) {
            $colsExists = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM dtb_csv WHERE csv_type_id = ?',
                [$csvTypeId]
            );

            if ($colsExists === 0) {
                // [fieldName, referenceFieldName, dispName, sortNo]
                $items = [
                    ['id',             null,   '商品ID',            1],
                    ['name',           null,   '商品名',            2],
                    ['code_min',       null,   '商品コード(下限)',  3],
                    ['code_max',       null,   '商品コード(上限)',  4],
                    ['price02_min',    null,   '販売価格(下限)',    5],
                    ['price02_max',    null,   '販売価格(上限)',    6],
                    ['Status',         'name', '公開ステータス',    7],
                    ['favorite_count', null,   'お気に入り数',      8],
                ];

                foreach ($items as [$fieldName, $referenceFieldName, $dispName, $sortNo]) {
                    $this->addSql(
                        "INSERT INTO dtb_csv (csv_type_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (?, 'Eccube\\\\Entity\\\\Product', ?, ?, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'csv')",
                        [$csvTypeId, $fieldName, $referenceFieldName, $dispName, $sortNo]
                    );
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        $csvTypeId = CustomCsvType::CSV_TYPE_FAVOURITE_PRODUCT;

        if ($schema->hasTable('dtb_csv')) {
            $this->addSql('DELETE FROM dtb_csv WHERE csv_type_id = ?', [$csvTypeId]);
        }

        if ($schema->hasTable('mtb_csv_type')) {
            $this->addSql('DELETE FROM mtb_csv_type WHERE id = ?', [$csvTypeId]);
        }
    }
}
