<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\User;

class ScheduleListQuery {
    private $user;
    private $channel;
    private $channelGroup;
    private $orderBy = [];
    /** @var \SuplaBundle\Entity\Main\Scene */
    private $scene;

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

    public function filterByScene(Scene $scene): ScheduleListQuery {
        $this->scene = $scene;
        return $this;
    }

    public function orderBy($column, $direction = 'ASC'): ScheduleListQuery {
        Assertion::inArray($column, ['id', 'caption', 'dateStart']);
        Assertion::inArray(strtolower($direction), ['asc', 'desc']);
        $this->orderBy = [$column => $direction];
        return $this;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function getChannel(): ?IODeviceChannel {
        return $this->channel;
    }

    public function getChannelGroup(): ?IODeviceChannelGroup {
        return $this->channelGroup;
    }

    public function getScene(): ?Scene {
        return $this->scene;
    }

    public function getOrderBy(): array {
        return $this->orderBy;
    }
}
