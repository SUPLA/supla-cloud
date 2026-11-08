<?php

namespace App\Tests\Command\Cyclic;

use App\Command\Cyclic\VirtualChannelsUpdateStateCommand;
use App\Entity\Main\IODeviceChannel;
use App\Model\VirtualChannel\VirtualChannelStateUpdater;
use App\Repository\IODeviceChannelRepository;
use App\Supla\SuplaAutodiscover;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class VirtualChannelsUpdateStateCommandTest extends TestCase {
    public function testUnregisteredCloudDoesNotQueryOrUpdateChannels() {
        $ad = $this->createMock(SuplaAutodiscover::class);
        $ad->method('isTarget')->willReturn(false);
        $repository = $this->createMock(IODeviceChannelRepository::class);
        $repository->expects($this->never())->method('findBy');
        $updater = $this->createMock(VirtualChannelStateUpdater::class);
        $updater->expects($this->never())->method('updateChannels');

        $tester = new CommandTester(new VirtualChannelsUpdateStateCommand($updater, $repository, $ad));

        $this->assertSame(0, $tester->execute([]));
        $this->assertSame('', $tester->getDisplay());
    }

    public function testRegisteredCloudUpdatesVirtualChannels() {
        $ad = $this->createMock(SuplaAutodiscover::class);
        $ad->method('isTarget')->willReturn(true);
        $channels = [$this->createMock(IODeviceChannel::class)];
        $repository = $this->createMock(IODeviceChannelRepository::class);
        $repository->expects($this->once())->method('findBy')->with(['isVirtual' => true])->willReturn($channels);
        $updater = $this->createMock(VirtualChannelStateUpdater::class);
        $updater->expects($this->once())->method('updateChannels')->with($channels);

        $tester = new CommandTester(new VirtualChannelsUpdateStateCommand($updater, $repository, $ad));

        $this->assertSame(0, $tester->execute([]));
    }
}
