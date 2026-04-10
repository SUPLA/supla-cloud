<?php

return [
    'em' => 'measurement_logs',
    'migrations_paths' => [
        'SuplaBundle\Migrations\TsDbMigration' => __DIR__ . '/../migrations/tsdb',
    ],
    'all_or_nothing' => true,
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
];
