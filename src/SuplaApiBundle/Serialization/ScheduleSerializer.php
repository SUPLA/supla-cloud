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

namespace SuplaApiBundle\Serialization;

use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ScheduleSerializer extends ObjectNormalizer {
    use SuplaServerAware;
    use CurrentUserAware;

    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ScheduleManager $scheduleManager
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
        $this->scheduleManager = $scheduleManager;
    }

    /**
     * @param Schedule $schedule
     * @inheritdoc
     */
    public function normalize($schedule, $format = null, array $context = []) {
        $normalized = parent::normalize($schedule, $format, $context);
        if (isset($context[self::GROUPS]) && is_array($context[self::GROUPS])) {
            if (in_array('closestExecutions', $context[self::GROUPS])) {
                $normalized['closestExecutions'] = $this->getClosestExecutions($schedule);
            }
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof Schedule;
    }

    private function getClosestExecutions(Schedule $schedule): array {
        return $this->scheduleManager->findClosestExecutions($schedule);
    }
}
