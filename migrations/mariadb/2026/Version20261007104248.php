<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Migrations\Migration;

use App\Migrations\NoWayBackMigration;

/**
 * Server-owned SupLAN M2 persistence; deliberately unmanaged by Cloud ORM.
 */
class Version20261007104248 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_suplan_device_state (
  device_id INT NOT NULL,
  user_id INT NOT NULL,
  current_root_epoch INT UNSIGNED NOT NULL,
  accepted_channels VARBINARY(512) NOT NULL,
  PRIMARY KEY (device_id),
  KEY suplan_device_user (user_id),
  CONSTRAINT suplan_state_device FOREIGN KEY (device_id)
    REFERENCES supla_iodevice(id) ON DELETE CASCADE,
  CONSTRAINT suplan_state_user FOREIGN KEY (user_id)
    REFERENCES supla_user(id) ON DELETE CASCADE,
  CONSTRAINT suplan_valid_root CHECK (current_root_epoch BETWEEN 1 AND 4294967294)
) ENGINE=InnoDB;');
        $this->addSql('CREATE TABLE supla_suplan_peer_association (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  source_device_id INT NOT NULL,
  destination_device_id INT NOT NULL,
  lifecycle TINYINT UNSIGNED NOT NULL DEFAULT 3,
  peer_generation INT UNSIGNED NOT NULL DEFAULT 1,
  acl_revision INT UNSIGNED NOT NULL DEFAULT 1,
  provisioned_root_epoch INT UNSIGNED NOT NULL DEFAULT 0,
  provisioned_peer_generation INT UNSIGNED NOT NULL DEFAULT 0,
  canonical_acl VARBINARY(534) NOT NULL,
  source_empty_acked TINYINT UNSIGNED NOT NULL DEFAULT 0,
  destination_empty_acked TINYINT UNSIGNED NOT NULL DEFAULT 0,
  source_removed TINYINT UNSIGNED NOT NULL DEFAULT 0,
  destination_removed TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY suplan_ordered_pair (source_device_id,destination_device_id),
  KEY suplan_source_replay (user_id,source_device_id,lifecycle),
  KEY suplan_destination_replay (user_id,destination_device_id,lifecycle),
  CONSTRAINT suplan_peer_user FOREIGN KEY (user_id)
    REFERENCES supla_user(id) ON DELETE CASCADE,
  CONSTRAINT suplan_peer_distinct CHECK (source_device_id<>destination_device_id),
  CONSTRAINT suplan_lifecycle CHECK (lifecycle IN (1,2,3)),
  CONSTRAINT suplan_counters CHECK (peer_generation>0 AND acl_revision>0),
  CONSTRAINT suplan_provisioned_root CHECK (provisioned_root_epoch<>4294967295),
  CONSTRAINT suplan_boolean_state CHECK (
    source_empty_acked IN (0,1) AND destination_empty_acked IN (0,1)
    AND source_removed IN (0,1) AND destination_removed IN (0,1))
) ENGINE=InnoDB;');
        $this->addSql('CREATE TABLE supla_suplan_grant (
  association_id BIGINT UNSIGNED NOT NULL,
  resource_type TINYINT UNSIGNED NOT NULL,
  resource_id INT UNSIGNED NOT NULL,
  origin_type SMALLINT UNSIGNED NOT NULL,
  origin_id BIGINT UNSIGNED NOT NULL,
  permissions TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (association_id,resource_type,resource_id,origin_type,origin_id),
  KEY suplan_grant_origin (origin_type,origin_id,association_id),
  KEY suplan_grant_resource (resource_type,resource_id,association_id),
  CONSTRAINT suplan_grant_parent FOREIGN KEY (association_id)
    REFERENCES supla_suplan_peer_association(id) ON DELETE CASCADE,
  CONSTRAINT suplan_grant_resource_type CHECK (resource_type IN (1,2)),
  CONSTRAINT suplan_grant_identity CHECK (resource_id>0 AND origin_type>0 AND origin_id>0),
  CONSTRAINT suplan_grant_permissions CHECK (permissions BETWEEN 1 AND 7)
) ENGINE=InnoDB;');
    }
}
