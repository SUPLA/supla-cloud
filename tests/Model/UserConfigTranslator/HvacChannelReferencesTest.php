<?php

namespace App\Tests\Model\UserConfigTranslator;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Model\UserConfigTranslator\HvacChannelReferences;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class HvacChannelReferencesTest extends TestCase {
    public function testAllSixLegacyReferencesUseDestinationDeviceAndMigrateIdempotently() {
        $device = new IODevice();
        $destination = new IODeviceChannel();
        EntityUtils::setField($destination, 'iodevice', $device);
        EntityUtils::setField($destination, 'id', 900);
        $channels = new ArrayCollection();
        $legacy = ['unrelated' => 'preserved'];
        $expected = ['unrelated' => 'preserved'];
        foreach (HvacChannelReferences::FIELDS as $number => $field) {
            $channel = new IODeviceChannel();
            EntityUtils::setField($channel, 'channelNumber', $number);
            EntityUtils::setField($channel, 'id', 100 + $number);
            $channels->add($channel);
            $legacy[$field . 'ChannelNo'] = $number;
            $expected[$field . 'ChannelId'] = 100 + $number;
        }
        EntityUtils::setField($device, 'channels', $channels);
        $destination->setUserConfig($legacy);
        $this->assertTrue(HvacChannelReferences::migrate($destination));
        $this->assertEquals($expected, $destination->getUserConfig());
        $this->assertFalse(HvacChannelReferences::migrate($destination));
        $this->assertEquals($expected, $destination->getUserConfig());
    }

    public function testCanonicalIdIncludingNullWinsOverLegacyAndMissingNumbersBecomeUnset() {
        $device = new IODevice();
        $destination = new IODeviceChannel();
        EntityUtils::setField($destination, 'iodevice', $device);
        $destination->setUserConfig([
            'mainThermometerChannelId' => 12345, 'mainThermometerChannelNo' => 0,
            'auxThermometerChannelId' => null, 'auxThermometerChannelNo' => 1,
            'binarySensorChannelNo' => 999, 'masterThermostatChannelNo' => -1,
            'pumpSwitchChannelNo' => null, 'heatOrColdSourceSwitchChannelNo' => '1',
        ]);
        HvacChannelReferences::migrate($destination);
        $this->assertSame([
            'mainThermometerChannelId' => 12345, 'auxThermometerChannelId' => null,
            'binarySensorChannelId' => null, 'masterThermostatChannelId' => null,
            'pumpSwitchChannelId' => null, 'heatOrColdSourceSwitchChannelId' => null,
        ], $destination->getUserConfig());
    }
}
