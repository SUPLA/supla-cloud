<?php
namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\IODeviceChannelRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ControllingAnyLockRelatedSensor {
    use CurrentUserAware;
    use Transactional;

    /** @var IODeviceChannelRepository */
    private $channelRepository;

    /** @required */
    public function setChannelRepository(IODeviceChannelRepository $channelRepository) {
        $this->channelRepository = $channelRepository;
    }

    protected function pairControllingAndSensorChannels(
        ChannelFunction $controllingFunction,
        ChannelFunction $sensorFunction,
        $controllingParamNo,
        $sensorParamNo,
        int $controllingId,
        int $sensorId
    ) {
        $this->transactional(function (EntityManagerInterface $em) use (
            $sensorFunction,
            $controllingFunction,
            $sensorParamNo,
            $controllingParamNo,
            $controllingId,
            $sensorId
        ) {
            $user = $this->getCurrentUserOrThrow();
            $controlling = $controllingId ? $this->channelRepository->findForUser($user, $controllingId) : null;
            $sensor = $sensorId ? $this->channelRepository->findForUser($user, $sensorId) : null;
            $currentSensorId = $controlling ? $controlling->getParam($controllingParamNo) : $sensorId;
            $currentControllingId = $sensor ? $sensor->getParam($sensorParamNo) : $controllingId;
            if ($controlling && $controlling->getFunction() != $controllingFunction) {
                $controllingId = 0;
            }
            if ($sensor && $sensor->getFunction() != $sensorFunction) {
                $sensorId = 0;
            }
            if (!$sensorId || !$controllingId) {
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
        });
    }
}
