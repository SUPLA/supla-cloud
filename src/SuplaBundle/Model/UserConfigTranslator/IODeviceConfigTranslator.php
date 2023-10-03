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
 *   @OA\Property(property="screenSaver", type="object",
 *     @OA\Property(property="mode", type="string", enum={"OFF", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE", "TEMPERATURE_TIME", "MAIN_AND_AUX_TEMPERATURE"}),
 *     @OA\Property(property="delay", type="integer", description="ms"),
 *   ),
 * )
 */
class IODeviceConfigTranslator {
    public function getConfig(IODevice $device): array {
        return $device->getUserConfig();
    }

    public function setConfig(IODevice $device, array $config): void {
        $currentConfig = $device->getUserConfig();
        foreach ($config as $settingName => $value) {
            Assertion::keyExists($currentConfig, $settingName, 'Cannot set this setting in this device: ' . $settingName);
            if ($settingName === 'statusLed') {
                Assertion::inArray($value, ['OFF_WHEN_CONNECTED', 'ALWAYS_OFF', 'ON_WHEN_CONNECTED']);
            }
            if ($settingName === 'screenBrightness') {
                if ($value !== 'auto') {
                    Assert::that($value)->integer()->between(0, 100);
                }
            }
            if ($settingName === 'buttonVolume') {
                Assert::that($value)->integer()->between(0, 100);
            }
            if ($settingName === 'userInterfaceDisabled') {
                Assert::that($value)->boolean();
            }
            if ($settingName === 'automaticTimeSync') {
                Assert::that($value)->boolean();
            }
            if ($settingName === 'screenSaver') {
                Assert::that($value)->isArray()->keyExists('mode')->keyExists('delay')->count(2);
                $availableModes = $device->getProperties()['screenSaverModesAvailable'] ?? [];
                Assertion::inArray($value['mode'], $availableModes);
                Assert::that($value['delay'])->integer()->between(500, 300000);
            }
            $device->setUserConfigValue($settingName, $value);
        }
    }
}
