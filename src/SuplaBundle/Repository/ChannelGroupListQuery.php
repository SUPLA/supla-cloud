<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use Doctrine\Common\Collections\Criteria;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\User;

class ChannelGroupListQuery {
    /** @var User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;
    /** @var Schedule */
    private $schedule;
    /** @var Scene */
    private $scene;
    private $orderBy = [];

    private function __construct() {
    }

    public static function create(): self {
        return new self();
    }

    public function filterByUser(User $user): self {
        $this->user = $user;
        return $this;
    }

    public function filterByChannel(IODeviceChannel $channel): self {
        $this->channel = $channel;
        return $this;
    }

    public function filterBySchedule(Schedule $schedule): self {
        $this->schedule = $schedule;
        return $this;
    }

    public function filterByScene(Scene $scene): self {
        $this->scene = $scene;
        return $this;
    }

    public function orderBy($column, $direction = 'ASC'): self {
        Assertion::inArray($column, ['id', 'caption']);
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

    public function getSchedule(): ?Schedule {
        return $this->schedule;
    }

    public function getScene(): ?Scene {
        return $this->scene;
    }

    public function getOrderBy(): array {
        return $this->orderBy;
    }

    public function buildCriteria(): Criteria {
        $criteria = Criteria::create();
        if ($this->user) {
            $criteria->where(Criteria::expr()->eq('user', $this->user));
        }
        if ($this->channel) {
            $criteria->where(Criteria::expr()->eq('channel', $this->channel));
        }
        if ($query->getChannelGroup()) {
            $criteria->where(Criteria::expr()->eq('channelGroup', $query->getChannelGroup()));
        }
        if ($query->getScene()) {
            $criteria->where(Criteria::expr()->eq('scene', $query->getScene()));
        }
        if ($query->getOrderBy()) {
            $criteria->orderBy($query->getOrderBy());
        }
        return $this->matching($criteria)->toArray();
    }
}
