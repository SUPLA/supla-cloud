<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;

class ScheduleListQuery {
    private $user;
    private $channel;
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

    public function getOrderBy(): array {
        return $this->orderBy;
    }
}
