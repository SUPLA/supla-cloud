<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Bugfix. Procedures should return `_id`
 */
class Version20200419190150 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_oauth_add_client_for_app`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_oauth_add_token_for_app`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_oauth_add_client_for_app` (IN `_random_id` VARCHAR(255) CHARSET utf8, 
IN `_secret` VARCHAR(255) CHARSET utf8, OUT `_id` INT(11))  NO SQL
BEGIN

SET @lck = 0;
SET @id_exists = 0;

SELECT GET_LOCK('oauth_add_client', 2) INTO @lck;

IF @lck = 1 THEN

 SELECT id INTO @id_exists FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;

  IF @id_exists <> 0 THEN
     SELECT @id_exists INTO _id;
  ELSE 
     INSERT INTO `supla_oauth_clients`(
         `random_id`, `redirect_uris`, 
         `secret`, `allowed_grant_types`, `type`) VALUES 
     (_random_id, 'a:0:{}', _secret,'a:2:{i:0;s:8:"password";i:1;s:13:"refresh_token";}',2);
     
     SELECT LAST_INSERT_ID() INTO _id;
     SELECT RELEASE_LOCK('oauth_add_client') INTO @lck;
  END IF;

END IF;

END
PROCEDURE
        );

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_oauth_add_token_for_app` (IN `_user_id` INT(11), IN `_token` VARCHAR(255) CHARSET utf8, 
IN `_expires_at` INT(11), IN `_access_id` INT(11), OUT `_id` INT(11))  NO SQL
BEGIN

SET @client_id = 0;

SELECT `id` INTO @client_id FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;

IF @client_id <> 0 AND EXISTS(SELECT 1 FROM `supla_accessid` WHERE `user_id` = _user_id AND `id` = _access_id) THEN 

  INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `access_id`) VALUES 
   (@client_id, _user_id, _token, _expires_at, 'channels_r channels_files', _access_id);
  SELECT LAST_INSERT_ID() INTO _id;

END IF;

END
PROCEDURE
        );
    }
}
