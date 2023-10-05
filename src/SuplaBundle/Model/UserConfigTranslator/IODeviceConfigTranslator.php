<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODevice;

/**
 * @OA\Schema(schema="DeviceConfig", description="Configuration of the IO Device.",
 *   @OA\Property(property="statusLed", type="string", enum={"OFF_WHEN_CONNECTED", "ALWAYS_OFF", "ON_WHEN_CONNECTED"}),
 *   @OA\Property(property="screenBrightness", oneOf={
 *     @OA\Schema(type="integer", minimum=0, maximum=100),
 *     @OA\Schema(type="string", enum={"auto"})
 *   }),
 *   @OA\Property(property="buttonVolume", type="integer", minimum=0, maximum=100),
 *   @OA\Property(property="userInterfaceDisabled", type="boolean"),
 *   @OA\Property(property="automaticTimeSync", type="boolean"),
 *   @OA\Property(property="homeScreen", type="object",
 *     @OA\Property(property="content", type="string", enum={"NONE", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE", "TEMPERATURE_TIME", "MAIN_AND_AUX_TEMPERATURE"}),
 *     @OA\Property(property="offDelay", type="integer", description="Number of seconds or `0` to disable."),
 *   ),
 *   @OA\Property(property="homeScreenContentAvailable", type="string", enum={"OFF", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE", "TEMPERATURE_TIME", "MAIN_AND_AUX_TEMPERATURE"}),
 * )
 */
class IODeviceConfigTranslator {
    public function getConfig(IODevice $device): array {
        $config = $device->getUserConfig();
        $properties = $device->getProperties();
        if ($properties['homeScreenContentAvailable'] ?? false) {
            $config['homeScreenContentAvailable'] = $properties['homeScreenContentAvailable'];
        }
        return $config;
    }

    public function setConfig(IODevice $device, array $config): void {
        $currentConfig = $device->getUserConfig();
        $config = array_diff_key($config, ['homeScreenContentAvailable' => '']);
        Assertion::allInArray(array_keys($config), array_keys($currentConfig));
        foreach ($config as $settingName => $value) {
            Assertion::keyExists($currentConfig, $settingName, 'Cannot set this setting in this device: ' . $settingName);
            if ($settingName === 'statusLed') {
                Assertion::inArray($value, ['OFF_WHEN_CONNECTED', 'ALWAYS_OFF', 'ON_WHEN_CONNECTED'], null, 'statusLed');
            }
            if ($settingName === 'screenBrightness') {
                if ($value !== 'auto') {
                    Assert::that($value, null, 'screenBrightness')->integer()->between(0, 100);
                }
            }
            if ($settingName === 'buttonVolume') {
                Assert::that($value, null, 'buttonVolume')->integer()->between(0, 100);
            }
            if ($settingName === 'userInterfaceDisabled') {
                Assert::that($value, null, 'userInterfaceDisabled')->boolean();
            }
            if ($settingName === 'automaticTimeSync') {
                Assert::that($value, null, 'automaticTimeSync')->boolean();
            }
            if ($settingName === 'homeScreen') {
                Assert::that($value, null, 'homeScreen')->isArray()->keyExists('content')->keyExists('offDelay')->count(2);
                $availableModes = $device->getProperties()['homeScreenContentAvailable'] ?? [];
                Assertion::inArray($value['content'], $availableModes, null, 'homeScreen.content');
                Assert::that($value['offDelay'], null, 'homeScreen.offDelay')->integer()->between(0, 3600);
            }
            $device->setUserConfigValue($settingName, $value);
        }
    }
}
