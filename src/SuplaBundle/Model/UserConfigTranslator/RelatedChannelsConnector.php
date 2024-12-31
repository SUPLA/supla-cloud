<?php
namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RelatedChannelsConnector extends UserConfigTranslator {
    use SuplaServerAware;

    private IODeviceChannelRepository $channelRepository;
    private EntityManagerInterface $em;

    public function __construct(IODeviceChannelRepository $channelRepository, EntityManagerInterface $em) {
        $this->channelRepository = $channelRepository;
        $this->em = $em;
    }

    public function getConfig(HasUserConfig $subject): array {
        $possibleRelations = self::getPossibleRelations()[$subject->getFunction()->getId()];
        $config = [];
        foreach ($possibleRelations as $possibleParamPairs) {
            foreach ($possibleParamPairs as $paramName => $params) {
                $config[$paramName] = $subject->getParam($params[0]) ?: null;
            }
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $user = $subject->getUser();
        $this->pairRelatedChannel($user, $subject, $config);
    }

    private function pairRelatedChannel(User $user, IODeviceChannel $channel, array $config) {
        $possibleRelations = self::getPossibleRelations()[$channel->getFunction()->getId()];
        foreach ($possibleRelations as $relatedFunction => $possibleParamPairs) {
            foreach ($possibleParamPairs as $paramName => $params) {
                if (!array_key_exists($paramName, $config)) {
                    continue;
                }
                [$thisParamNo, $relatedParamNo] = $params;
                $this->clearAlreadyChosenChannels($config, $paramName, $possibleParamPairs);
                $thisId = $channel->getId();
                $relatedId = $config[$paramName] ?: 0;
                $thisChannel = $thisId ? $this->channelRepository->findForUserOrNull($user, $thisId) : null;
                $relatedChannel = $relatedId ? $this->channelRepository->findForUserOrNull($user, $relatedId) : null;
                $currentRelatedId = $thisChannel ? $thisChannel->getParam($thisParamNo) : $relatedId;
                $currentThisId = $relatedChannel ? $relatedChannel->getParam($relatedParamNo) : $thisId;
                if ($relatedChannel && $relatedChannel->getFunction()->getId() !== $relatedFunction) {
                    continue;
                }
                if (!$relatedChannel || !$thisChannel) {
                    $relatedId = $thisId = 0;
                }
                if ($currentRelatedId && $currentRelatedId != $relatedId) {
                    try {
                        $currentSensor = $this->channelRepository->findForUser($user, $currentRelatedId);
                        $currentSensor->setParam($relatedParamNo, 0);
                        $currentSensor->setUserConfigValue($paramName, null);
                        $this->em->persist($currentSensor);
                    } catch (NotFoundHttpException $e) {
                    }
                }
                if ($currentThisId && $currentThisId != $thisId) {
                    try {
                        $currentControlling = $this->channelRepository->findForUser($user, $currentThisId);
                        $currentControlling->setParam($thisParamNo, 0);
                        $currentControlling->setUserConfigValue($paramName, null);
                        $this->em->persist($currentControlling);
                    } catch (NotFoundHttpException $e) {
                    }
                }
                if ($thisChannel && $relatedChannel) {
                    Assertion::eq(
                        $thisChannel->getLocation()->getId(),
                        $relatedChannel->getLocation()->getId(),
                        'Channels that are meant to work with each other must be in the same location.' // i18n
                    );
                }
                if ($thisChannel && $currentRelatedId != $relatedId) {
                    $thisChannel->setParam($thisParamNo, $relatedId);
                    $thisChannel->setUserConfigValue($paramName, $relatedId);
                    $this->em->persist($thisChannel);
                }
                if ($relatedChannel && $currentThisId != $thisId) {
                    $relatedChannel->setParam($relatedParamNo, $thisId);
                    $relatedChannel->setUserConfigValue($paramName, $relatedId);
                    $this->em->persist($relatedChannel);
                }
            }
        }
    }

    private function clearAlreadyChosenChannels(array &$config, string $paramName, array $possibleParamPairs) {
        foreach (array_keys($possibleParamPairs) as $possibleParamName) {
            if ($possibleParamName != $paramName) {
                if (($config[$possibleParamName] ?? null) === ($config[$paramName] ?? null)) {
                    $config[$possibleParamName] = null;
                }
            }
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), array_keys(self::getPossibleRelations()));
    }

    public static function getPossibleRelations(): array {
        $relations = [
            'openingSensorChannelId' => [
                [ChannelFunction::OPENINGSENSOR_DOOR, 1, ChannelFunction::CONTROLLINGTHEDOORLOCK, 2],
                [ChannelFunction::OPENINGSENSOR_ROOFWINDOW, 1, ChannelFunction::CONTROLLINGTHEROOFWINDOW, 2],
                [ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER, 1, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, 2],
                [ChannelFunction::OPENINGSENSOR_GATEWAY, 1, ChannelFunction::CONTROLLINGTHEGATEWAYLOCK, 2],
                [ChannelFunction::OPENINGSENSOR_GARAGEDOOR, 1, ChannelFunction::CONTROLLINGTHEGARAGEDOOR, 2],
                [ChannelFunction::OPENINGSENSOR_GATE, 1, ChannelFunction::CONTROLLINGTHEGATE, 2],
            ],
            'openingSensorSecondaryChannelId' => [
                [ChannelFunction::OPENINGSENSOR_GATE, 2, ChannelFunction::CONTROLLINGTHEGATE, 3],
                [ChannelFunction::OPENINGSENSOR_GARAGEDOOR, 2, ChannelFunction::CONTROLLINGTHEGARAGEDOOR, 3],
            ],
        ];
        $relationsMapped = [];
        foreach ($relations as $relationName => $possibleRelations) {
            foreach ($possibleRelations as $relation) {
                $functionsA = is_array($relation[0]) ? $relation[0] : [$relation[0]];
                $functionsB = is_array($relation[2]) ? $relation[2] : [$relation[2]];
                foreach ($functionsA as $functionA) {
                    foreach ($functionsB as $functionB) {
                        $relationsMapped[$functionA][$functionB][$relationName] = [$relation[1], $relation[3]];
                        $relationsMapped[$functionB][$functionA][$relationName] = [$relation[3], $relation[1]];
                    }
                }
            }
        }
        return $relationsMapped;
    }
}
