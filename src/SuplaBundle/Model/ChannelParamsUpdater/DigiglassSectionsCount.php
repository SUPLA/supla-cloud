<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class DigiglassSectionsCount extends RangeParamsUpdater {
    const MAX_SECTIONS = 7;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::DIGIGLASS_VERTICAL(), ChannelFunction::DIGIGLASS_HORIZONTAL()],
            1,
            self::MAX_SECTIONS
        );
    }
}
