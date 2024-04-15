<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Utils\JsonArrayObject;

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
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigHvacThermostat"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigGeneralPurposeMeasurement"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigGeneralPurposeMeter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigRollerShutter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigFacadeBlinds"),
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
        foreach ($this->translators as $translator) {
            if ($translator->supports($subject)) {
                $translator->clearConfig($subject);
            }
        }
        $subject->setUserConfig([]);
    }

    public function getPublicConfig(HasUserConfig $subject) {
        $config = $this->getConfig($subject);
        $config = (new JsonArrayObject($config))->jsonSerialize();
        if (is_array($config) && isset($config['googleHome']) && isset($config['googleHome']['pin'])) {
            unset($config['googleHome']['pin']);
        }
        return $config;
    }
}
