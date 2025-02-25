<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\ChannelState;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\SubDevice;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelFunctionBitsFlags as Flags;
use SuplaBundle\Enums\ChannelFunctionBitsFlist as Functions;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\IoDeviceFlags;
use SuplaBundle\Tests\AnyFieldSetter;

class DevicesFixture extends SuplaFixture {
    const ORDER = LocationsFixture::ORDER + 1;
    const NUMBER_OF_RANDOM_DEVICES = 15;

    const DEVICE_SONOFF = 'deviceSonoff';
    const DEVICE_FULL = 'deviceFull';
    const DEVICE_RGB = 'deviceRgb';
    const DEVICE_HVAC = 'deviceHvac';
    const DEVICE_SUPLER = 'deviceSupler';
    const DEVICE_EVERY_FUNCTION = 'ALL-IN-ONE MEGA DEVICE';
    const DEVICE_MEASUREMENTS = 'deviceMeasurements';
    const RANDOM_DEVICE_PREFIX = 'randomDevice';

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var Generator */
    private $faker;

    public function setObjectManager(ObjectManager $m): self {
        $this->entityManager = $m;
        $this->faker = Factory::create('pl_PL');
        return $this;
    }

    public function load(ObjectManager $manager) {
        $this->setObjectManager($manager);
        $location = $this->getReference(LocationsFixture::LOCATION_OUTSIDE);
        $this->createDeviceSonoff($location);
        $this->createDeviceFull($this->getReference(LocationsFixture::LOCATION_GARAGE));
        $this->createDeviceRgb($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $this->createEveryFunctionDevice($location, self::DEVICE_EVERY_FUNCTION);
        $hvac = $this->createDeviceHvac($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $this->setReference(self::DEVICE_HVAC, $hvac);
        $this->createDeviceGeneralPurposeMeasurement($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $this->createDeviceGateway($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $this->createDeviceSeptic($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $this->createDevice('EMPTY DEVICE', $location, []);
        $device = $this->createEveryFunctionDevice($location, 'SECOND MEGA DEVICE');
        foreach ($this->faker->randomElements($device->getChannels(), 3) as $noFunctionChannel) {
            $noFunctionChannel->setFunction(ChannelFunction::NONE());
            $this->entityManager->persist($noFunctionChannel);
        }
        $this->createDeviceManyGates($location);
        $nonDeviceLocations = [null, $location, $this->getReference(LocationsFixture::LOCATION_BEDROOM)];
        for ($i = 0; $i < self::NUMBER_OF_RANDOM_DEVICES; $i++) {
            $name = strtoupper(implode('-', $this->faker->words($this->faker->numberBetween(1, 3))));
            $device = $this->createDeviceFull($this->getReference(LocationsFixture::LOCATION_GARAGE), $name);
            foreach ($device->getChannels() as $channel) {
                $channel->setLocation($nonDeviceLocations[rand(0, count($nonDeviceLocations) - 1)]);
                $manager->persist($channel);
            }
            $this->setReference(self::RANDOM_DEVICE_PREFIX . $i, $device);
        }
        $suplerDevice = $this->createEveryFunctionDevice($this->getReference(LocationsFixture::LOCATION_SUPLER), 'SUPLER MEGA DEVICE', '');
        $this->setReference(self::DEVICE_SUPLER, $suplerDevice);
        $this->createDeviceLocked($location);
        $manager->flush();
    }

    protected function createDeviceSonoff(Location $location): IODevice {
        $device = $this->createDevice('SONOFF-DS by@fracz', $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, ['funcList' => Functions::LIGHTSWITCH | Functions::POWERSWITCH]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::ACTION_TRIGGER, ChannelFunction::ACTION_TRIGGER],
        ], self::DEVICE_SONOFF);
        $at = $device->getChannels()[2];
        $at->setParam1($device->getChannels()[0]->getId());
        $this->entityManager->persist($at);
        return $device;
    }

    protected function createDeviceFull(Location $location, $name = 'UNI-MODULE'): IODevice {
        $device = $this->createDevice($name, $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, ['funcList' => Functions::LIGHTSWITCH | Functions::POWERSWITCH]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK, ['funcList' => Functions::getAllFeaturesFlag()]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE, ['funcList' => Functions::getAllFeaturesFlag()]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, [
                'funcList' => Functions::CONTROLLINGTHEROLLERSHUTTER,
                'flags' => Flags::RECALIBRATE_ACTION_AVAILABLE,
                'userConfig' => json_encode([
                    'timeMargin' => -1,
                    'motorUpsideDown' => false,
                    'buttonsUpsideDown' => false,
                ]),
            ]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEFACADEBLIND, [
                'funcList' => Functions::CONTROLLINGTHEFACADEBLIND | Functions::CONTROLLINGTHEROLLERSHUTTER,
                'userConfig' => json_encode([
                    'tiltControlType' => 'STANDS_IN_POSITION_WHILE_TILTING',
                    'timeMargin' => -1,
                    'motorUpsideDown' => false,
                    'buttonsUpsideDown' => false,
                ]),
            ]],
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATEWAY],
            [ChannelType::SENSORNC, ChannelFunction::OPENINGSENSOR_DOOR],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::ACTION_TRIGGER, ChannelFunction::ACTION_TRIGGER],
            [ChannelType::BRIDGE, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, ['funcList' => Functions::getAllFeaturesFlag(), 'flags' => Flags::AUTO_CALIBRATION_AVAILABLE]],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER, ['flags' => Flags::RESET_COUNTERS_ACTION_AVAILABLE]],
            [ChannelType::ACTION_TRIGGER, ChannelFunction::ACTION_TRIGGER],
        ], self::DEVICE_FULL);
        $at = $device->getChannels()[10];
        $at->setParam1($device->getChannels()[4]->getId());
        $this->entityManager->persist($at);
        return $device;
    }

    public function createEveryFunctionDevice(
        Location $location,
        $name = 'ALL-IN-ONE MEGA DEVICE'
    ): IODevice {
        $functionableTypes = array_filter(ChannelType::values(), function (ChannelType $type) {
            return count(ChannelType::functions()[$type->getValue()] ?? []);
        });
        $channels = array_values(array_map(function (ChannelType $type) {
            return array_map(function (ChannelFunction $function) use ($type) {
                return [$type->getValue(), $function->getValue(), $this->getDefaultChannelConfig($function)];
            }, ChannelType::functions()[$type->getValue()]);
        }, $functionableTypes));
        $channels = call_user_func_array('array_merge', $channels);
        return $this->createDevice($name, $location, $channels, $name);
    }

    protected function createDeviceRgb(Location $location): IODevice {
        return $this->createDevice('RGB-801', $location, [
            [ChannelType::RGBLEDCONTROLLER, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::RGBLEDCONTROLLER, ChannelFunction::RGBLIGHTING],
        ], self::DEVICE_RGB);
    }

    private function createDeviceManyGates(Location $location) {
        $channels = [];
        for ($i = 0; $i < 10; $i++) {
            $channels[] = [
                ChannelType::RELAY,
                ChannelFunction::CONTROLLINGTHEGATE,
                ['funcList' => Functions::getAllFeaturesFlag()],
            ];
        }
        $channels[8][2]['conflictDetails'] = '{"missing": true}';
        $channels[3][2]['conflictDetails'] = '{"type": 2000}';
        return $this->createDevice('OH-MY-GATES. This device also has ridiculously long name!', $location, $channels, 'gatesDevice');
    }

    public function createDeviceHvac(Location $location) {
        $sampleQuarters1 = array_map(
            'intval',
            str_split(
                str_repeat(
                    str_repeat('0', 6 * 4) .
                    str_repeat('1', 2 * 4) .
                    str_repeat('3', 6 * 4) .
                    str_repeat('2', 2 * 4) .
                    str_repeat('1', 6 * 4) .
                    str_repeat('0', 2 * 4),
                    5
                ) . str_repeat(
                    str_repeat('0', 8 * 4 + 2) .
                    str_repeat('2', 12 * 4) .
                    str_repeat('4', 2 * 4) .
                    str_repeat('0', 4 + 2),
                    2
                )
            )
        );
        $sampleQuarters2 = array_map('intval', str_split(str_replace('4', '1', implode('', $sampleQuarters1))));
        $hvac = $this->createDevice('HVAC-Monster', $location, [
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_THERMOSTAT,
                [
                    'funcList' => Functions::HVAC_THERMOSTAT | Functions::HVAC_DOMESTIC_HOT_WATER,
                    'properties' => json_encode([
                        'availableAlgorithms' => ['ON_OFF_SETPOINT_MIDDLE', 'ON_OFF_SETPOINT_AT_MOST', 'PID'],
                        'temperatures' => [
                            'roomMin' => 1000,
                            'roomMax' => 3900,
                            'auxMin' => 500,
                            'auxMax' => 5000,
                            'histeresisMin' => 100,
                            'histeresisMax' => 500,
                            'autoOffsetMin' => 100,
                            'autoOffsetMax' => 200,
                        ],
                        'localUILockingCapabilities' => ['FULL', 'TEMPERATURE'],
                    ]),
                    'userConfig' => json_encode([
                        'subfunction' => 'HEAT',
                        'mainThermometerChannelNo' => 1,
                        'auxThermometerChannelNo' => null,
                        'usedAlgorithm' => 'ON_OFF_SETPOINT_MIDDLE',
                        'temperatureControlType' => 'ROOM_TEMPERATURE',
                        'temperatures' => [
                            'freezeProtection' => 1000,
                            'heatProtection' => 3300,
                            'histeresis' => 200,
                            'auxMinSetpoint' => 550,
                            'auxMaxSetpoint' => 4000,
                        ],
                        'weeklySchedule' => [
                            'programSettings' => [
                                '1' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 2400, 'setpointTemperatureCool' => 0],
                                '2' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 2100, 'setpointTemperatureCool' => 0],
                                '3' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 1800, 'setpointTemperatureCool' => 0],
                                '4' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 2800, 'setpointTemperatureCool' => 0],
                            ],
                            'quarters' => $sampleQuarters1,
                        ],
                        'altWeeklySchedule' => [
                            'programSettings' => [
                                '1' => ['mode' => 'COOL', 'setpointTemperatureHeat' => 0, 'setpointTemperatureCool' => 2400],
                                '2' => ['mode' => 'COOL', 'setpointTemperatureHeat' => 0, 'setpointTemperatureCool' => 2100],
                                '3' => ['mode' => 'COOL', 'setpointTemperatureHeat' => 0, 'setpointTemperatureCool' => 1800],
                                '4' => ['mode' => 'COOL', 'setpointTemperatureHeat' => 0, 'setpointTemperatureCool' => 2800],
                            ],
                            'quarters' => $sampleQuarters1,
                        ],
                    ]),
                ],
            ],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
                [
                    'funcList' => Functions::HVAC_THERMOSTAT_HEAT_COOL,
                    'properties' => json_encode([
                        'availableAlgorithms' => ['ON_OFF_SETPOINT_MIDDLE'],
                        'temperatures' => [
                            'roomMin' => 1100,
                            'roomMax' => 4000,
                            'auxMin' => 500,
                            'auxMax' => 5000,
                            'histeresisMin' => 100,
                            'histeresisMax' => 500,
                            'autoOffsetMin' => 100,
                            'autoOffsetMax' => 2000,
                        ],
                        'localUILockingCapabilities' => ['TEMPERATURE'],
                    ]),
                    'userConfig' => json_encode([
                        'mainThermometerChannelNo' => 0,
                        'auxThermometerChannelNo' => 1,
                        'auxThermometerType' => 'FLOOR',
                        'temperatureControlType' => 'NOT_SUPPORTED',
                        'antiFreezeAndOverheatProtectionEnabled' => true,
                        'auxMinMaxSetpointEnabled' => true,
                        'temperatureSetpointChangeSwitchesToManualMode' => true,
                        'usedAlgorithm' => 'ON_OFF_SETPOINT_MIDDLE',
                        'minOnTimeS' => 60,
                        'minOffTimeS' => 120,
                        'outputValueOnError' => 42,
                        'temperatures' => [
                            'freezeProtection' => 1100,
                            'eco' => 1800,
                            'comfort' => 2000,
                            'boost' => 2500,
                            'heatProtection' => 3300,
                            'histeresis' => 200,
                            'belowAlarm' => 1200,
                            'aboveAlarm' => 3600,
                            'auxMinSetpoint' => 1000,
                            'auxMaxSetpoint' => 2000,
                        ],
                        'weeklySchedule' => [
                            'programSettings' => [
                                '1' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 2100, 'setpointTemperatureCool' => 0],
                                '2' => ['mode' => 'COOL', 'setpointTemperatureHeat' => 0, 'setpointTemperatureCool' => 2400],
                                '3' => ['mode' => 'HEAT_COOL', 'setpointTemperatureHeat' => 1800, 'setpointTemperatureCool' => 2200],
                                '4' => ['mode' => 'NOT_SET', 'setpointTemperatureHeat' => 0, 'setpointTemperatureCool' => 0],
                            ],
                            'quarters' => $sampleQuarters2,
                        ],
                    ]),
                ],
            ],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
                [
                    'subDeviceId' => 1,
                    'funcList' => Functions::HVAC_THERMOSTAT_HEAT_COOL | Functions::HVAC_DOMESTIC_HOT_WATER |
                        Functions::HVAC_THERMOSTAT | Functions::HVAC_THERMOSTAT_DIFFERENTIAL,
                    'properties' => json_encode([
                        'availableAlgorithms' => ['ON_OFF_SETPOINT_MIDDLE', 'ON_OFF_SETPOINT_AT_MOST'],
                        'temperatures' => [
                            'roomMin' => 1000,
                            'roomMax' => 4000,
                            'auxMin' => 500,
                            'auxMax' => 5000,
                            'histeresisMin' => 100,
                            'histeresisMax' => 500,
                            'autoOffsetMin' => 100,
                            'autoOffsetMax' => 2000,
                        ],
                        'readOnlyConfigFields' => [
                            //"mainThermometerChannelNo",
                            //"auxThermometerChannelNo",
                            "binarySensorChannelNo",
                            //"auxThermometerType",
                            //"antiFreezeAndOverheatProtectionEnabled",
                            "usedAlgorithm",
                            //"minOnTimeS",
                            //"minOffTimeS",
                            //"outputValueOnError",
                            "subfunction",
                            //"auxMinMaxSetpointEnabled",
                            //"useSeparateHeatCoolOutputs",
                            //"masterThermostatChannelNo",
                            //"heatOrColdSourceSwitchChannelNo",
                            //"pumpSwitchChannelNo",
                            //"temperatureSetpointChangeSwitchesToManualMode",
                        ],
                        'hiddenConfigFields' => [
                            //"mainThermometerChannelNo",
//                            "auxThermometerChannelNo",
                            //"binarySensorChannelNo",
                            //"auxThermometerType",
                            "antiFreezeAndOverheatProtectionEnabled",
                            //"usedAlgorithm",
                            //"minOnTimeS",
                            //"minOffTimeS",
                            //"outputValueOnError",
                            //"subfunction",
                            //"auxMinMaxSetpointEnabled",
                            //"useSeparateHeatCoolOutputs",
                            //"masterThermostatChannelNo",
                            //"heatOrColdSourceSwitchChannelNo",
                            "pumpSwitchChannelNo",
                            //"temperatureSetpointChangeSwitchesToManualMode",
                        ],
                        'readOnlyTemperatureConfigFields' => [
                            //"freezeProtection",
                            "eco",
                            //"comfort",
                            //"boost",
                            //"heatProtection",
                            //"histeresis",
                            //"belowAlarm",
                            //"aboveAlarm",
                            //"auxMinSetpoint",
                            //"auxMaxSetpoint",
                        ],
                        'hiddenTemperatureConfigFields' => [
                            //"freezeProtection",
                            //"eco",
                            "comfort",
                            //"boost",
                            //"heatProtection",
                            //"histeresis",
                            //"belowAlarm",
                            //"aboveAlarm",
                            //"auxMinSetpoint",
                            //"auxMaxSetpoint",
                        ],
                    ]),
                    'userConfig' => json_encode([
                        'mainThermometerChannelNo' => null,
                        'auxThermometerChannelNo' => null,
                        'binarySensorChannelNo' => 5,
                        'usedAlgorithm' => 'ON_OFF_SETPOINT_AT_MOST',
                        'antiFreezeAndOverheatProtectionEnabled' => true,
                        'temperatures' => [
                            'freezeProtection' => 1100,
                            'eco' => 1800,
                            'comfort' => 2000,
                            'boost' => 2500,
                        ],
                        'weeklySchedule' => [
                            'programSettings' => [
                                '1' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 2400, 'setpointTemperatureCool' => 0],
                                '2' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 2100, 'setpointTemperatureCool' => 0],
                                '3' => ['mode' => 'HEAT', 'setpointTemperatureHeat' => 1800, 'setpointTemperatureCool' => 0],
                                '4' => ['mode' => 'NOT_SET', 'setpointTemperatureHeat' => 2200, 'setpointTemperatureCool' => 0],
                            ],
                            'quarters' => $sampleQuarters2,
                        ],
                    ]),
                    'flags' => Flags::BATTERY_COVER_AVAILABLE,
                ],
            ],
            [ChannelType::SENSORNO, ChannelFunction::HOTELCARDSENSOR, ['subDeviceId' => 1]],
            [ChannelType::SENSORNO, ChannelFunction::NONE],
            [ChannelType::THERMOSTATHEATPOLHOMEPLUS, ChannelFunction::THERMOSTATHEATPOLHOMEPLUS],
            [ChannelType::RELAY2XG5LA1A, ChannelFunction::PUMPSWITCH],
            [ChannelType::RELAY2XG5LA1A, ChannelFunction::HEATORCOLDSOURCESWITCH],
            [ChannelType::RELAY2XG5LA1A, ChannelFunction::PUMPSWITCH],
        ], '');
        AnyFieldSetter::set($hvac, [
            'userConfig' => json_encode([
                'statusLed' => 'OFF_WHEN_CONNECTED',
                'screenBrightness' => ['level' => 13],
                'buttonVolume' => 14,
                'userInterface' => ['disabled' => false],
                'automaticTimeSync' => false,
                'powerStatusLed' => 'DISABLED',
                'homeScreen' => ['content' => 'TEMPERATURE', 'offDelay' => 60, 'offDelayType' => 'ALWAYS_ENABLED'],
            ]),
            'properties' => json_encode([
                'homeScreenContentAvailable' => [
                    'NONE', 'TEMPERATURE', 'HUMIDITY', 'TIME', 'TIME_DATE', 'TEMPERATURE_TIME', 'MAIN_AND_AUX_TEMPERATURE',
                ],
            ]),
            'flags' => IoDeviceFlags::ALWAYS_ALLOW_CHANNEL_DELETION,
        ]);
        $this->entityManager->persist($hvac);
        $state = new ChannelState($hvac->getChannels()[3]);
        EntityUtils::setField($state, 'state', '{"batteryPowered": true}');
        $this->entityManager->persist($state);
        $this->entityManager->persist(AnyFieldSetter::set(new SubDevice(), [
            'id' => 1,
            'device' => $hvac,
            'regDate' => new DateTime(),
            'name' => 'Thermostat with hotel card sensor',
            'softwareVersion' => '2.' . rand(0, 50),
            'productCode' => strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'serialNumber' => $this->faker->uuid(),
        ]));
        return $hvac;
    }

    public function createDeviceGeneralPurposeMeasurement(Location $location) {
        $device = $this->createDevice('Measurement Freak', $location, [
            [
                ChannelType::GENERAL_PURPOSE_MEASUREMENT,
                ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
                [
                    'caption' => 'KPOP',
                    'properties' => json_encode([
                        'defaultValueDivider' => 78,
                        'defaultValueMultiplier' => 910,
                        'defaultValueAdded' => 1112,
                        'defaultValuePrecision' => 4,
                        'defaultUnitBeforeValue' => 'XCVB',
                        'defaultUnitAfterValue' => 'GHJK',
                    ]),
                    'userConfig' => json_encode([
                        'valueDivider' => 12,
                        'valueMultiplier' => 34,
                        'valueAdded' => 56,
                        'valuePrecision' => 2,
                        'refreshIntervalMs' => 2000,
                        'unitBeforeValue' => 'ABCD',
                        'unitAfterValue' => 'EFGH',
                        'noSpaceAfterValue' => false,
                        'keepHistory' => true,
                        'chartType' => 'CANDLE',
                    ]),
                ],
            ],
            [
                ChannelType::GENERAL_PURPOSE_METER,
                ChannelFunction::GENERAL_PURPOSE_METER,
                [
                    'caption' => 'KLOP rosnący',
                    'properties' => json_encode([
                        'defaultValueDivider' => 78,
                        'defaultValueMultiplier' => 910,
                        'defaultValueAdded' => 1112,
                        'defaultValuePrecision' => 4,
                        'defaultUnitBeforeValue' => 'XCVB',
                        'defaultUnitAfterValue' => 'GHJK',
                    ]),
                    'userConfig' => json_encode([
                        'valueDivider' => 0,
                        'valueMultiplier' => 34,
                        'valueAdded' => 56,
                        'valuePrecision' => 3,
                        'refreshIntervalMs' => 3000,
                        'unitBeforeValue' => 'ABCD',
                        'unitAfterValue' => 'EFGH',
                        'noSpaceAfterValue' => false,
                        'keepHistory' => true,
                        'chartType' => 'BAR',
                        'includeValueAddedInHistory' => true,
                        'fillMissingData' => true,
                        'counterType' => 'ALWAYS_INCREMENT',
                    ]),
                    'flags' => Flags::RESET_COUNTERS_ACTION_AVAILABLE,
                ],
            ],
            [
                ChannelType::GENERAL_PURPOSE_METER,
                ChannelFunction::GENERAL_PURPOSE_METER,
                [
                    'caption' => 'KLOP malejący',
                    'properties' => json_encode([
                        'defaultValueDivider' => 1,
                        'defaultValueMultiplier' => 1,
                        'defaultValueAdded' => 0,
                        'defaultValuePrecision' => 0,
                        'defaultUnitBeforeValue' => '',
                        'defaultUnitAfterValue' => 'ciastek',
                    ]),
                    'userConfig' => json_encode([
                        'valueDivider' => 1,
                        'valueMultiplier' => 1,
                        'valueAdded' => 0,
                        'valuePrecision' => 0,
                        'refreshIntervalMs' => 4000,
                        'unitBeforeValue' => '',
                        'unitAfterValue' => 'ciastek',
                        'noSpaceAfterValue' => false,
                        'keepHistory' => true,
                        'chartType' => 'LINEAR',
                        'includeValueAddedInHistory' => true,
                        'fillMissingData' => true,
                        'counterType' => 'ALWAYS_DECREMENT',
                    ]),
                ],
            ],
        ], self::DEVICE_MEASUREMENTS);
        $this->entityManager->persist($device);
        return $device;
    }

    public function createDeviceGateway(Location $location) {
        $device = $this->createDevice('GATEWAY-JEJ', $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, ['funcList' => Functions::LIGHTSWITCH | Functions::POWERSWITCH]],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, [
                'funcList' => Functions::LIGHTSWITCH | Functions::POWERSWITCH,
                'subDeviceId' => 1,
                'flags' => ChannelFunctionBitsFlags::IDENTIFY_SUBDEVICE_AVAILABLE | ChannelFunctionBitsFlags::RESTART_SUBDEVICE_AVAILABLE,
            ]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER, ['subDeviceId' => 1]],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, [
                'funcList' => Functions::LIGHTSWITCH | Functions::POWERSWITCH,
                'subDeviceId' => 2,
                'flags' => ChannelFunctionBitsFlags::RESTART_SUBDEVICE_AVAILABLE,
            ]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER, ['subDeviceId' => 2]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE, ['funcList' => Functions::getAllFeaturesFlag(), 'subDeviceId' => 3]],
            [ChannelType::SENSORNO, ChannelFunction::NONE, ['subDeviceId' => 3]],
            [ChannelType::SENSORNO, ChannelFunction::NONE, ['subDeviceId' => 3]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER, ['subDeviceId' => 4]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER, ['subDeviceId' => 4]],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_THERMOSTAT,
                [
                    'subDeviceId' => 4,
                    'funcList' => Functions::HVAC_THERMOSTAT,
                    'properties' => json_encode(['readOnlyConfigFields' => ['auxThermometerChannelNo']]),
                    'userConfig' => json_encode(['mainThermometerChannelNo' => 8, 'auxThermometerChannelNo' => 9]),
                ],
            ],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_THERMOSTAT,
                [
                    'subDeviceId' => 4,
                    'funcList' => Functions::HVAC_THERMOSTAT,
                    'properties' => json_encode(['readOnlyConfigFields' => ['auxThermometerChannelNo']]),
                    'userConfig' => json_encode(['mainThermometerChannelNo' => 8, 'auxThermometerChannelNo' => 9]),
                ],
            ],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_THERMOSTAT,
                [
                    'subDeviceId' => 4,
                    'funcList' => Functions::HVAC_THERMOSTAT,
                    'properties' => json_encode(['readOnlyConfigFields' => ['auxThermometerChannelNo']]),
                    'userConfig' => json_encode(['mainThermometerChannelNo' => 8, 'auxThermometerChannelNo' => 9]),
                ],
            ],
            [
                ChannelType::HVAC,
                ChannelFunction::HVAC_THERMOSTAT,
                [
                    'subDeviceId' => 4,
                    'funcList' => Functions::HVAC_THERMOSTAT,
                    'properties' => json_encode(['readOnlyConfigFields' => ['auxThermometerChannelNo']]),
                    'userConfig' => json_encode(['mainThermometerChannelNo' => 8, 'auxThermometerChannelNo' => 9]),
                ],
            ],
        ]);
        AnyFieldSetter::set($device, [
            'flags' => IoDeviceFlags::ALWAYS_ALLOW_CHANNEL_DELETION
                | IoDeviceFlags::BLOCK_ADDING_CHANNELS_AFTER_DELETION
                | IoDeviceFlags::IDENTIFY_DEVICE_AVAILABLE
                | IoDeviceFlags::PAIRING_SUBDEVICES_AVAILABLE,
        ]);
        $this->entityManager->persist($device);
        $this->entityManager->persist(AnyFieldSetter::set(new SubDevice(), [
            'id' => 1,
            'device' => $device,
            'regDate' => new DateTime(),
            'name' => 'My Cool Subdevice',
            'softwareVersion' => '2.' . rand(0, 50),
            'productCode' => strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'serialNumber' => $this->faker->uuid(),
        ]));
        $this->entityManager->persist(AnyFieldSetter::set(new SubDevice(), [
            'id' => 3,
            'device' => $device,
            'regDate' => new DateTime(),
            'name' => 'Two Channels Subdeivce',
            'softwareVersion' => '3.' . rand(0, 50),
            'productCode' => strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'serialNumber' => $this->faker->uuid(),
        ]));
        $this->entityManager->persist(AnyFieldSetter::set(new SubDevice(), [
            'id' => 4,
            'device' => $device,
            'regDate' => new DateTime(),
            'name' => 'Floor Heating Controller',
            'softwareVersion' => '6.' . rand(0, 50),
            'productCode' => strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'serialNumber' => $this->faker->uuid(),
        ]));
        $this->entityManager->persist($device);
        return $device;
    }

    public function createDeviceSeptic(Location $location) {
        $device = $this->createDevice('SEPTIC-DEVICE', $location, [
            [
                ChannelType::CONTAINER,
                ChannelFunction::SEPTIC_TANK,
                [
                    'userConfig' => json_encode([
                        'warningAboveLevel' => 20,
                        'alarmAboveLevel' => 30,
                        'warningBelowLevel' => 40,
                        'alarmBelowLevel' => 50,
                        'muteAlarmSoundWithoutAdditionalAuth' => false,
                        'sensors' => [
                            ['channelNo' => 1, 'fillLevel' => 20],
                            ['channelNo' => 2, 'fillLevel' => 30],
                            ['channelNo' => 3, 'fillLevel' => 40],
                            ['channelNo' => 4, 'fillLevel' => 50],
                        ],
                    ]),
                ],
            ],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR, ['subDeviceId' => 11]],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR, ['subDeviceId' => 11]],
            [ChannelType::SENSORNO, ChannelFunction::CONTAINER_LEVEL_SENSOR, ['subDeviceId' => 11]],
            [
                ChannelType::VALVEOPENCLOSE,
                ChannelFunction::VALVEOPENCLOSE,
                [
                    'userConfig' => json_encode([
                        'sensorChannelNumbers' => [12, 13, 14],
                    ]),
                    'flags' => ChannelFunctionBitsFlags::FLOOD_SENSORS_SUPPORTED,
                ],
            ],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR, ['subDeviceId' => 23]],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR, ['subDeviceId' => 23]],
            [ChannelType::SENSORNO, ChannelFunction::FLOOD_SENSOR, ['subDeviceId' => 23]],
            [
                ChannelType::CONTAINER,
                ChannelFunction::WATER_TANK,
                [
                    'caption' => 'Full fill level tank',
                    'userConfig' => json_encode([
                        'warningAboveLevel' => 20,
                        'alarmAboveLevel' => 30,
                        'warningBelowLevel' => 40,
                        'alarmBelowLevel' => 50,
                        'muteAlarmSoundWithoutAdditionalAuth' => false,
                        'sensors' => [
                            ['channelNo' => 1, 'fillLevel' => 20],
                        ],
                    ]),
                    'flags' => ChannelFunctionBitsFlags::TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE,
                ],
            ],
        ]);
        $this->entityManager->persist(AnyFieldSetter::set(new SubDevice(), [
            'id' => 23,
            'device' => $device,
            'regDate' => new DateTime(),
            'name' => 'Flood sensor sub device',
            'softwareVersion' => '6.' . rand(0, 50),
            'productCode' => strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'serialNumber' => $this->faker->uuid(),
        ]));
        AnyFieldSetter::set($device, [
            'flags' => IoDeviceFlags::ALWAYS_ALLOW_CHANNEL_DELETION,
        ]);
        $this->entityManager->persist($device);
        return $device;
    }

    public function createDeviceLocked(Location $location): IODevice {
        $device = $this->createDevice('LOCKED-DEVICE', $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, ['funcList' => Functions::LIGHTSWITCH | Functions::POWERSWITCH]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
        AnyFieldSetter::set($device, ['flags' => IoDeviceFlags::DEVICE_LOCKED | IoDeviceFlags::ENTER_CONFIGURATION_MODE_AVAILABLE]);
        $this->entityManager->persist($device);
        $this->entityManager->flush();
        return $device;
    }

    private function createDevice(string $name, Location $location, array $channelTypes, string $registerAs = ''): IODevice {
        $device = new IODevice();
        AnyFieldSetter::set($device, [
            'name' => $name,
            'guid' => rand(0, 9999999),
            'regDate' => new DateTime(),
            'lastConnected' => new DateTime(),
            'regIpv4' => implode('.', [rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255)]),
            'softwareVersion' => '2.' . rand(0, 50),
            'protocolVersion' => '2.' . rand(0, 50),
            'location' => $location,
            'user' => $location->getUser(),
            'flags' => IoDeviceFlags::ENTER_CONFIGURATION_MODE_AVAILABLE
                | IoDeviceFlags::SLEEP_MODE_ENABLED
                | IoDeviceFlags::REMOTE_RESTART_AVAILABLE,
            'userConfig' => '{"statusLed": "ON_WHEN_CONNECTED"}',
        ]);
        $this->entityManager->persist($device);
        $this->entityManager->flush();
        foreach ($channelTypes as $channelNumber => $channelData) {
            $channel = new IODeviceChannel();
            if ($this->faker->boolean()) {
                $channel->setCaption($this->faker->sentence(3));
            }
            AnyFieldSetter::set($channel, [
                'iodevice' => $device,
                'user' => $location->getUser(),
                'type' => $channelData[0],
                'function' => $channelData[1],
                'channelNumber' => $channelNumber,
            ]);
            if (isset($channelData[2])) {
                AnyFieldSetter::set($channel, $channelData[2]);
            }
            $this->setChannelProperties($channel);
            $this->entityManager->persist($channel);
            $this->entityManager->flush();
        }
        $this->entityManager->refresh($device);
        if ($registerAs && $this->referenceRepository) {
            $this->setReference($registerAs, $device);
        }
        return $device;
    }

    private function setChannelProperties(IODeviceChannel $channel) {
        $channelProperties = [];
        switch ($channel->getType()->getId()) {
            case ChannelType::ACTION_TRIGGER:
                $possibleTriggers = ['TURN_ON', 'TURN_OFF', 'TOGGLE_X1', 'TOGGLE_X2', 'TOGGLE_X3', 'TOGGLE_X4', 'TOGGLE_X5',
                    'HOLD', 'SHORT_PRESS_X1', 'SHORT_PRESS_X2', 'SHORT_PRESS_X3', 'SHORT_PRESS_X4', 'SHORT_PRESS_X5'];
                $possibleTriggersForChannel = $this->faker->randomElements($possibleTriggers, $this->faker->numberBetween(1, 5));
                $channelProperties = ['actionTriggerCapabilities' => $possibleTriggersForChannel];
                break;
            case ChannelType::ELECTRICITYMETER:
                $possibleCounters = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy',
                    'forwardActiveEnergyBalanced', 'reverseActiveEnergyBalanced'];
                $numberOfCounters = $this->faker->numberBetween(2, count($possibleCounters));
                $countersAvailable = $this->faker->randomElements($possibleCounters, $numberOfCounters);
                $channelProperties['countersAvailable'] = $countersAvailable;
                $channelProperties['availableCTTypes'] = ["100A_33mA", "200A_66mA", "400A_133mA"];
                $channelProperties['availablePhaseLedTypes'] = [
                    "OFF", "VOLTAGE_PRESENCE", "VOLTAGE_PRESENCE_INVERTED", "VOLTAGE_LEVEL", "POWER_ACTIVE_DIRECTION",
                ];
                break;
            case ChannelType::IMPULSECOUNTER:
                $channelProperties['ocr'] = [
                    'authKey' => 'aaabbbccc',
                    'availableLightingModes' => ['OFF', 'AUTO', 'ALWAYS_ON'],
                ];
                break;
        }
        if ($channelProperties) {
            EntityUtils::setField($channel, 'properties', json_encode($channelProperties));
        }
    }

    private function getDefaultChannelConfig(ChannelFunction $function): array {
        $configs = [
            ChannelFunction::VERTICAL_BLIND => [
                'funcList' => Functions::CONTROLLINGTHEFACADEBLIND | Functions::VERTICAL_BLIND,
                'userConfig' => json_encode([
                    'tiltControlType' => 'STANDS_IN_POSITION_WHILE_TILTING',
                    'timeMargin' => -1,
                    'motorUpsideDown' => false,
                    'buttonsUpsideDown' => false,
                ]),
            ],
            ChannelFunction::PROJECTOR_SCREEN => [
                'funcList' => Functions::PROJECTOR_SCREEN | Functions::TERRACE_AWNING,
                'flags' => Flags::RECALIBRATE_ACTION_AVAILABLE | Flags::ROLLER_SHUTTER_STEP_BY_STEP_ACTIONS | Flags::AUTO_CALIBRATION_AVAILABLE,
                'userConfig' => json_encode([
                    'timeMargin' => -1,
                    'buttonsUpsideDown' => false,
                ]),
            ],
        ];
        return $configs[$function->getId()] ?? [];
    }
}
