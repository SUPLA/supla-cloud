<?php
namespace SuplaBundle\Migrations\Migration;

use App\Migrations\NoWayBackMigration;

/** ADR-013 cutover. Final unreleased SupLAN grant schema; no upgrade path from milestone preview databases. */
class Version20261016120000 extends NoWayBackMigration {
    public function isTransactional(): bool {
        return false; // MariaDB DDL commits implicitly; this migration creates schema/procedures only.
    }

    public function migrate() {
        foreach (explode(';', file_get_contents(__DIR__ . '/cloud-grants/schema.sql')) as $statement) {
            if (trim($statement)) {
                $this->addSql($statement);
            }
        }
        $this->addSql(file_get_contents(__DIR__ . '/cloud-grants/valid.sql'));
        foreach (glob(__DIR__ . '/cloud-grants/supla_*.sql') as $procedure) {
            $this->addSql(file_get_contents($procedure));
        }
    }
}
