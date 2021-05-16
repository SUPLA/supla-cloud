<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\IODeviceChannelRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RelatedChannelsConnector implements SingleChannelParamsUpdater {
    use Transactional;

    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(IODeviceChannelRepository $channelRepository) {
        $this->channelRepository = $channelRepository;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $this->transactional(function (EntityManagerInterface $em) use ($channel, $updatedChannel) {
            $user = $channel->getUser();
            $this->pairRelatedChannel($em, $user, $channel, $updatedChannel);
        });
    }

    public function pairRelatedChannel(EntityManagerInterface $em, User $user, IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $possibleRelations = self::getPossibleRelations()[$channel->getFunction()->getId()];
        foreach ($possibleRelations as $relatedFunction => $possibleParamPairs) {
            foreach ($possibleParamPairs as $params) {
                list($controllingParamNo, $sensorParamNo) = $params;
                $this->clearAlreadyChosenChannels($updatedChannel, $possibleParamPairs, $sensorParamNo, $controllingParamNo);
                $controllingId = $channel->getId();
                $sensorId = $updatedChannel->getParam($controllingParamNo);
                $controlling = $controllingId ? $this->channelRepository->findForUserOrNull($user, $controllingId) : null;
                $sensor = $sensorId ? $this->channelRepository->findForUserOrNull($user, $sensorId) : null;
                $currentSensorId = $controlling ? $controlling->getParam($controllingParamNo) : $sensorId;
                $currentControllingId = $sensor ? $sensor->getParam($sensorParamNo) : $controllingId;
                if ($sensor && $sensor->getFunction()->getId() !== $relatedFunction) {
                    continue;
                }
                if (!$sensor || !$controlling) {
                    $sensorId = $controllingId = 0;
                }
                if ($currentSensorId && $currentSensorId != $sensorId) {
                    try {
                        $currentSensor = $this->channelRepository->findForUser($user, $currentSensorId);
                        $currentSensor->setParam($sensorParamNo, 0);
                        $em->persist($currentSensor);
                    } catch (NotFoundHttpException $e) {
                    }
                }
                if ($currentControllingId && $currentControllingId != $controllingId) {
                    try {
                        $currentControlling = $this->channelRepository->findForUser($user, $currentControllingId);
                        $currentControlling->setParam($controllingParamNo, 0);
                        $em->persist($currentControlling);
                    } catch (NotFoundHttpException $e) {
                    }
                }
                if ($controlling && $currentSensorId != $sensorId) {
                    $controlling->setParam($controllingParamNo, $sensorId);
                    $em->persist($controlling);
                }
                if ($sensor && $currentControllingId != $controllingId) {
                    $sensor->setParam($sensorParamNo, $controllingId);
                    $em->persist($sensor);
                }
            }
        }
    }

    private function clearAlreadyChosenChannels(IODeviceChannel $updatedChannel, $possibleParamPairs, $sensorParamNo, $controllingParamNo) {
        foreach ($possibleParamPairs as $possibleParamPair) {
            list($possibleControllingParamNo, $possibleSensorParamNo) = $possibleParamPair;
            if ($possibleControllingParamNo != $controllingParamNo) {
                if ($updatedChannel->getParam($controllingParamNo) == $updatedChannel->getParam($possibleControllingParamNo)) {
                    $updatedChannel->setParam($possibleControllingParamNo, 0);
                }
            }
            if ($possibleSensorParamNo != $sensorParamNo) {
                if ($updatedChannel->getParam($sensorParamNo) == $updatedChannel->getParam($possibleSensorParamNo)) {
                    $updatedChannel->setParam($possibleSensorParamNo, 0);
                }
            }
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), array_keys(self::getPossibleRelations()));
    }

    public static function getPossibleRelations(): array {
        $relations = [
            [ChannelFunction::OPENINGSENSOR_DOOR, 1, ChannelFunction::CONTROLLINGTHEDOORLOCK, 2],
            [ChannelFunction::OPENINGSENSOR_ROOFWINDOW, 1, ChannelFunction::CONTROLLINGTHEROOFWINDOW, 2],
            [ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER, 1, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, 2],
            [ChannelFunction::OPENINGSENSOR_GATEWAY, 1, ChannelFunction::CONTROLLINGTHEGATEWAYLOCK, 2],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR, 1, ChannelFunction::CONTROLLINGTHEGARAGEDOOR, 2],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR, 2, ChannelFunction::CONTROLLINGTHEGARAGEDOOR, 3],
            [ChannelFunction::OPENINGSENSOR_GATE, 1, ChannelFunction::CONTROLLINGTHEGATE, 2],
            [ChannelFunction::OPENINGSENSOR_GATE, 2, ChannelFunction::CONTROLLINGTHEGATE, 3],
            [
                [ChannelFunction::POWERSWITCH, ChannelFunction::LIGHTSWITCH],
                1,
                [ChannelFunction::ELECTRICITYMETER, ChannelFunction::GASMETER, ChannelFunction::WATERMETER, ChannelFunction::HEATMETER],
                4,
            ],
        ];
        $relationsMapped = [];
        foreach ($relations as $relation) {
            $functionsA = is_array($relation[0]) ? $relation[0] : [$relation[0]];
            $functionsB = is_array($relation[2]) ? $relation[2] : [$relation[2]];
            foreach ($functionsA as $functionA) {
                foreach ($functionsB as $functionB) {
                    $relationsMapped[$functionA][$functionB][] = [$relation[1], $relation[3]];
                    $relationsMapped[$functionB][$functionA][] = [$relation[3], $relation[1]];
                }
            }
        }
        return $relationsMapped;
    }
}
