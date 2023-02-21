<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;

/**
 * @OA\Schema(schema="ChannelConfigControllingTheGate", description="Config for `CONTROLLINGTHEGARAGEDOOR` and `CONTROLLINGTHEGATE`.",
 *   @OA\Property(property="timeSettingAvailable", type="boolean", readOnly=true),
 *   @OA\Property(property="relayTimeMs", type="integer"),
 *   @OA\Property(property="openingSensorChannelId", type="integer"),
 *   @OA\Property(property="openingSensorSecondaryChannelId", type="integer"),
 *   @OA\Property(property="numberOfAttemptsToOpen", type="integer", minimum=1, maximum=5),
 *   @OA\Property(property="numberOfAttemptsToClose", type="integer", minimum=1, maximum=5),
 *   @OA\Property(property="stateVerificationMethodActive", type="boolean"),
 *   @OA\Property(property="closingRule",
 *      @OA\Property(property="enabled", type="boolean"),
 *      @OA\Property(property="maxTimeOpen", type="integer", minimum=300, maximum=28800),
 *      @OA\Property(property="activeFrom", type="string", format="date-time"),
 *      @OA\Property(property="activeTo", type="string", format="date-time"),
 *      @OA\Property(property="activeHours", ref="#/components/schemas/ActiveHoursDef"),
 *      @OA\Property(property="activeNow", type="boolean", readOnly=true),
 *   ),
 * )
 */
class RelayTimeMsChannelParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    private const TIMES = [
        ChannelFunction::CONTROLLINGTHEDOORLOCK => [500, 120000],
        ChannelFunction::CONTROLLINGTHEGARAGEDOOR => [500, 2000],
        ChannelFunction::CONTROLLINGTHEGATE => [500, 2000],
        ChannelFunction::CONTROLLINGTHEGATEWAYLOCK => [500, 120000],
    ];

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'relayTimeMs' => $channel->getParam1() ?: 500,
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($channel->getFlags()),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('relayTimeMs', $config) || !$channel->getParam1()) {
            $times = self::TIMES[$channel->getFunction()->getId()] ?? [500, 10000];
            $channel->setParam1($this->getValueInRange($config['relayTimeMs'] ?? 0, $times[0], $times[1]));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEDOORLOCK,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
        ]);
    }
}
