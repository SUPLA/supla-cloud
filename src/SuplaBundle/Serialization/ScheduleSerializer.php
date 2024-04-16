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

namespace SuplaBundle\Serialization;

use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ScheduleSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use SuplaServerAware;
    use CurrentUserAware;
    use NormalizerAwareTrait;

    private ScheduleManager $scheduleManager;
    private ChannelActionExecutor $channelActionExecutor;

    public function __construct(ScheduleManager $scheduleManager, ChannelActionExecutor $channelActionExecutor) {
        parent::__construct();
        $this->scheduleManager = $scheduleManager;
        $this->channelActionExecutor = $channelActionExecutor;
    }

    /**
     * @param \SuplaBundle\Entity\Main\Schedule $schedule
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $schedule, array $context) {
        $subjectType = $schedule->getSubjectType()->getValue();
        $normalized['subjectType'] = $subjectType;
        $normalized['subjectId'] = $schedule->getSubject()->getId();
        $normalized['mode'] = $schedule->getMode()->getValue();
        if ($this->isSerializationGroupRequested('closestExecutions', $context)) {
            $normalized['closestExecutions'] = $this->getClosestExecutions($schedule, null, $context);
        }
        if (is_array($normalized['config'] ?? false)) {
            foreach ($normalized['config'] as &$configEntry) {
                $configEntry['action']['param'] = $this->channelActionExecutor->transformActionParamsForApi(
                    $schedule->getSubject(),
                    new ChannelFunctionAction($configEntry['action']['id']),
                    $configEntry['action']['param'] ?? []
                );
            }
        }
    }

    private function getClosestExecutions(Schedule $schedule, $format, array $context): array {
        $closest = $this->scheduleManager->findClosestExecutions($schedule);
        return [
            'past' => $this->normalizer->normalize($closest['past'], $format, $context),
            'future' => $this->normalizer->normalize($closest['future'], $format, $context),
        ];
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof Schedule;
    }
}
