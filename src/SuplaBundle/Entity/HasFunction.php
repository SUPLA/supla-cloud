<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Enums\ChannelFunction;

interface HasFunction {
    public function getId(): int;

    public function getFunction(): ChannelFunction;

    /**
     * Returns a footprint of this functionable item for identification in SUPLA Server commands.
     * See SuplaServer#setValue for more details.
     * @return int[]
     */
    public function getServerFootprint(): array;
}
