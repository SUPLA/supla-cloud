<?php
namespace SuplaBundle\Migrations\Migration;

use App\Migrations\NoWayBackMigration;

/** STREAM request provenance; existing canonical pairs and consent origins are unchanged. */
class Version20261013215600 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_resource_access_decision
            ADD COLUMN IF NOT EXISTS requested_delivery_mode INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE supla_resource_access_decision
            ADD CONSTRAINT IF NOT EXISTS resource_access_delivery CHECK(
                requested_delivery_mode=1 OR (requested_delivery_mode=2 AND (permissions=1
                    OR (status IN (\'ACCEPTED\',\'REMOVED\') AND (permissions & 3)<>0))))');
        $this->addSql('DROP TRIGGER IF EXISTS resource_access_decision_revision');
        $this->addSql("CREATE TRIGGER resource_access_decision_revision BEFORE UPDATE ON supla_resource_access_decision
            FOR EACH ROW BEGIN
                SET NEW.requested_at=OLD.requested_at;
                SET NEW.last_decision_at=IF(OLD.status<>NEW.status,UTC_TIMESTAMP(),OLD.last_decision_at);
                IF NEW.pair_id<>OLD.pair_id OR NEW.id<>OLD.id THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Access decision identity is immutable';
                END IF;
                IF OLD.status<>'PENDING' AND NEW.permissions<>OLD.permissions THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Only pending access may change permissions';
                END IF;
                IF OLD.status='REMOVED' OR
                    (OLD.status='ACCEPTED' AND NEW.status NOT IN ('ACCEPTED','REMOVED')) OR
                    (OLD.status='REJECTED' AND NEW.status NOT IN ('REJECTED','ACCEPTED','REMOVED')) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid access decision transition';
                END IF;
                IF OLD.status<>NEW.status OR OLD.permissions<>NEW.permissions
                    OR OLD.requested_delivery_mode<>NEW.requested_delivery_mode THEN
                    IF OLD.revision>=2147483647 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Access decision revision exhausted';
                    END IF;
                    SET NEW.revision=OLD.revision+1;
                ELSE
                    SET NEW.revision=OLD.revision;
                END IF;
            END");
    }
}
