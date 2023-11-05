<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Connection;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Fix order of user icons.
 */
class Version20190401151822 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql(
            'UPDATE supla_user_icons SET image4=(@temp:=image4), image4 = image1, image1 = @temp WHERE func IN (:func)',
            ['func' => [ChannelFunction::DIMMERANDRGBLIGHTING]],
            ['func' => Connection::PARAM_INT_ARRAY]
        );
        $this->addSql(
            'UPDATE supla_user_icons SET image3=(@temp:=image3), image3 = image2, image2 = @temp WHERE func IN (:func)',
            ['func' => [ChannelFunction::DIMMERANDRGBLIGHTING]],
            ['func' => Connection::PARAM_INT_ARRAY]
        );
        $this->addSql(
            'UPDATE supla_user_icons SET image2=(@temp:=image2), image2 = image1, image1 = @temp WHERE func IN (:func)',
            ['func' => [ChannelFunction::POWERSWITCH, ChannelFunction::LIGHTSWITCH, ChannelFunction::DIMMER, ChannelFunction::RGBLIGHTING, ChannelFunction::STAIRCASETIMER]],
            ['func' => Connection::PARAM_INT_ARRAY]
        );
    }
}
