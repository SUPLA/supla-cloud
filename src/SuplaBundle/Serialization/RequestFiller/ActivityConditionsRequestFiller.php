<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\HasActivityConditions;

trait ActivityConditionsRequestFiller {
    protected function fillActivityConditions(array $data, HasActivityConditions $subject): void {
        if (array_key_exists('activeFrom', $data)) {
            $activeFrom = $data['activeFrom'];
            if ($activeFrom) {
                Assertion::string($activeFrom);
                Assertion::integer(strtotime($activeFrom));
                $subject->setActiveFrom(new \DateTime($activeFrom));
            } else {
                $subject->setActiveFrom(null);
            }
        }
        if (array_key_exists('activeTo', $data)) {
            $activeFrom = $data['activeTo'];
            if ($activeFrom) {
                Assertion::string($activeFrom);
                Assertion::integer(strtotime($activeFrom));
                $subject->setActiveTo(new \DateTime($activeFrom));
            } else {
                $subject->setActiveTo(null);
            }
        }
        if (array_key_exists('activeHours', $data)) {
            $activeHours = $data['activeHours'];
            if ($activeHours) {
                Assertion::isArray($activeHours);
                $subject->setActiveHours($activeHours);
            } else {
                $subject->setActiveHours(null);
            }
        }
        if (array_key_exists('activityConditions', $data)) {
            $conditions = $data['activityConditions'];
            if ($conditions) {
                Assertion::isArray($conditions);
                foreach ($conditions as $condition) {
                    Assertion::isArray($condition);
                    Assertion::allIsArray($condition);
                    Assertion::allCount($condition, 1);
                    foreach ($condition as $threshold) {
                        Assertion::isArray($threshold);
                        Assertion::count($threshold, 1);
                        Assertion::inArray(key($threshold), ['beforeSunrise', 'afterSunrise', 'beforeSunset', 'afterSunset']);
                        Assertion::integer(current($threshold));
                    }
                }
                $subject->setActivityConditions($conditions);
            } else {
                $subject->setActivityConditions(null);
            }
        }
    }
}
