<?php

namespace plugin\saiai\app\tool;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use support\think\Db;

#[AsTool(name: 'get_tables', description: 'List all tables in the database.', method: 'getTables')]
#[AsTool(name: 'get_table_schema', description: 'Get the schema (columns) of a specific table.', method: 'getTableSchema')]
class DbTool
{
    public function getTables(): string
    {
        try {
            $list = Db::query('show table status');
            $tables = [];
            $tables[] = "| Table Name | Engine | Rows | Comment |";
            $tables[] = "| :--- | :--- | :--- | :--- |";

            foreach ($list as $item) {
                $tables[] = sprintf(
                    "| %s | %s | %s | %s |",
                    $item['Name'],
                    $item['Engine'] ?? '',
                    $item['Rows'] ?? 0,
                    $item['Comment'] ?? ''
                );
            }
            return implode("\n", $tables);
        } catch (\Throwable $e) {
            return "Error listing tables: " . $e->getMessage();
        }
    }

    public function getTableSchema(string $table): string
    {
        try {
            // Basic sanitization
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $table)) {
                return "Invalid table name.";
            }

            $list = Db::query("SHOW FULL COLUMNS FROM `{$table}`");
            $columns = [];
            $columns[] = "| Field | Type | Key | Null | Default | Comment |";
            $columns[] = "| :--- | :--- | :--- | :--- | :--- | :--- |";

            foreach ($list as $column) {
                $columns[] = sprintf(
                    "| %s | %s | %s | %s | %s | %s |",
                    $column['Field'],
                    $column['Type'],
                    $column['Key'],
                    $column['Null'],
                    $column['Default'] ?? 'NULL',
                    $column['Comment']
                );
            }
            return implode("\n", $columns);
        } catch (\Throwable $e) {
            return "Error getting schema for table '{$table}': " . $e->getMessage();
        }
    }
}
