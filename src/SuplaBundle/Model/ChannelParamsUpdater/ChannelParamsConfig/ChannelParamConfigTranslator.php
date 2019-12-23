<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;

class ChannelParamConfigTranslator {
    /** @var ChannelParamTranslator[] */
    private $translators = [];

    public function __construct(iterable $translators) {
        $this->translators = $translators;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        $config = [];
        foreach ($this->translators as $translator) {
            if ($translator->supports($channel)) {
                $config = array_merge($config, $translator->getConfigFromParams($channel));
            }
        }
        return $config;
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config): void {
        foreach ($this->translators as $translator) {
            if ($translator->supports($channel)) {
                $translator->setParamsFromConfig($channel, $config);
            }
        }
    }
}
