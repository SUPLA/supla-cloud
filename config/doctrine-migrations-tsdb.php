<?php

return [
    'em' => 'logs_tsdb',
    'migrations_paths' => [
        'SuplaBundle\Migrations\TsDbMigration' => __DIR__ . '/../migrations/tsdb',
    ],
    'custom_template' => __DIR__ . '/../migrations/migration.tpl',
    'all_or_nothing' => true,
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
];
