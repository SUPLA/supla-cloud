<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;

/**
 * @OA\Schema(schema="ChannelConfig", description="Configuration of the channel.",
 *   oneOf={
 *     @OA\Schema(type="object"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigActionTrigger"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigElectricityMeter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigHumidityAndThermometer"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigHumidity"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigThermometer"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigImpulseCounter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigInvertedLogic"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigStaircaseTimer"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigControllingTheGate"),
 *   }
 * )
 */
class SubjectConfigTranslator {
    /** @var UserConfigTranslator[] */
    private $translators = [];

    public function __construct(iterable $translators) {
        $this->translators = $translators;
    }

    public function getConfig(HasUserConfig $subject): array {
        $config = [];
        foreach ($this->translators as $translator) {
            if ($translator->supports($subject)) {
                $config = array_merge($config, $translator->getConfig($subject));
            }
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config): void {
        foreach ($this->translators as $translator) {
            if ($translator->supports($subject)) {
                $translator->setConfig($subject, $config);
            }
        }
    }

    public function clearConfig(HasUserConfig $subject) {
        $config = $this->getConfig($subject);
        $config = array_map(function () {
            return null;
        }, $config);
        $this->setConfig($subject, $config);
    }
}
