<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\User;

class ScheduleListQuery {
    private $user;
    private $channel;
    private $channelGroup;
    private $orderBy = [];

    private function __construct() {
    }

    public static function create(): ScheduleListQuery {
        return new self();
    }

    public function filterByUser(User $user): ScheduleListQuery {
        $this->user = $user;
        return $this;
    }

    public function filterByChannel(IODeviceChannel $channel): ScheduleListQuery {
        $this->channel = $channel;
        return $this;
    }

    public function filterByChannelGroup(IODeviceChannelGroup $channelGroup): ScheduleListQuery {
        $this->channelGroup = $channelGroup;
        return $this;
    }

    public function orderBy($column, $direction = 'ASC'): ScheduleListQuery {
        Assertion::inArray($column, ['id', 'caption', 'dateStart']);
        Assertion::inArray(strtolower($direction), ['asc', 'desc']);
        $this->orderBy = [$column => $direction];
        return $this;
    }

    /** @return User|null */
    public function getUser() {
        return $this->user;
    }

    /** @return IODeviceChannel|null */
    public function getChannel() {
        return $this->channel;
    }

    /** @return IODeviceChannelGroup|null */
    public function getChannelGroup() {
        return $this->channelGroup;
    }

    public function getOrderBy(): array {
        return $this->orderBy;
    }
}
