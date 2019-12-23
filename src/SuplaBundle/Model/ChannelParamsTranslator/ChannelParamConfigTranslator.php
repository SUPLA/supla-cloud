<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

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

    public function clearConfig(IODeviceChannel $channel) {
        $config = $this->getConfigFromParams($channel);
        $config = array_map(function () {
            return null;
        }, $config);
        $this->setParamsFromConfig($channel, $config);
    }
}
