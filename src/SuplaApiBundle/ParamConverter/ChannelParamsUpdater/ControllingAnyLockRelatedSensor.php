<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\IODeviceChannelRepository;

abstract class ControllingAnyLockRelatedSensor implements SingleChannelParamsUpdater {
    use CurrentUserAware;
    use Transactional;

    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelFunction */
    private $controllingFunction;
    /** @var ChannelFunction */
    protected $sensorFunction;

    public function __construct(ChannelFunction $controllingFunction, ChannelFunction $sensorFunction) {
        $this->controllingFunction = $controllingFunction;
        $this->sensorFunction = $sensorFunction;
    }

    /** @required */
    public function setChannelRepository(IODeviceChannelRepository $channelRepository) {
        $this->channelRepository = $channelRepository;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $this->pairControllingAndSensorChannels($channel->getId(), $updatedChannel->getParam2());
    }

    protected function pairControllingAndSensorChannels(int $controllingId, int $sensorId) {
        $this->transactional(function (EntityManagerInterface $em) use ($controllingId, $sensorId) {
            $user = $this->getCurrentUserOrThrow();
            $controlling = $controllingId ? $this->channelRepository->findForUser($user, $controllingId) : null;
            $sensor = $sensorId ? $this->channelRepository->findForUser($user, $sensorId) : null;
            $currentSensorId = $controlling ? $controlling->getParam2() : $sensorId;
            $currentControllingId = $sensor ? $sensor->getParam1() : $controllingId;
            if ($controlling && $controlling->getFunction() != $this->controllingFunction) {
                $controllingId = 0;
            }
            if ($sensor && $sensor->getFunction() != $this->sensorFunction) {
                $sensorId = 0;
            }
            if (!$sensorId || !$controllingId) {
                $sensorId = $controllingId = 0;
            }
            if ($currentSensorId && $currentSensorId != $sensorId) {
                $currentSensor = $this->channelRepository->findForUser($user, $currentSensorId);
                $currentSensor->setParam1(0);
                $em->persist($currentSensor);
            }
            if ($currentControllingId && $currentControllingId != $controllingId) {
                $currentControlling = $this->channelRepository->findForUser($user, $currentControllingId);
                $currentControlling->setParam2(0);
                $em->persist($currentControlling);
            }
            if ($controlling && $currentSensorId != $sensorId) {
                $controlling->setParam2($sensorId);
                $em->persist($controlling);
            }
            if ($sensor && $currentControllingId != $controllingId) {
                $sensor->setParam1($controllingId);
                $em->persist($sensor);
            }
        });
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction() == $this->controllingFunction;
    }
}
