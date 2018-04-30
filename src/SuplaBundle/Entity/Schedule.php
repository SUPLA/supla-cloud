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

namespace SuplaBundle\Entity;

use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ScheduleMode;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ScheduleRepository")
 * @ORM\Table(name="supla_schedule", indexes={
 *     @ORM\Index(name="next_calculation_date_idx", columns={"next_calculation_date"}),
 *     @ORM\Index(name="enabled_idx", columns={"enabled"}),
 *     @ORM\Index(name="date_start_idx", columns={"date_start"})
 * })
 */
class Schedule {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="schedules")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="time_expression", type="string", length=100, nullable=false)
     * @Groups({"basic"})
     */
    private $timeExpression;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="schedules")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false)
     * @Groups({"channel", "iodevice", "location"})
     */
    private $channel;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255)
     * @Groups({"basic"})
     */
    private $actionParam;

    /**
     * @ORM\Column(name="mode", type="string", length=15, nullable=false)
     * @Groups({"basic"})
     */
    private $mode;

    /**
     * @ORM\Column(name="date_start", type="utcdatetime", nullable=false)
     * @Groups({"basic"})
     */
    private $dateStart;

    /**
     * @ORM\Column(name="date_end", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $dateEnd;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    protected $enabled = false;

    /**
     * @ORM\Column(name="next_calculation_date", type="utcdatetime", nullable=true)
     */
    private $nextCalculationDate;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="retry", type="boolean", nullable=false, options={"default" : 1})
     * @Groups({"basic"})
     */
    protected $retry = true;

    public function __construct(User $user = null, array $data = []) {
        $this->user = $user;
        if (count($data)) {
            $this->fill($data);
        }
    }

    public function fill(array $data) {
        Assert::that($data)->notEmptyKey('timeExpression');
        $this->setTimeExpression($data['timeExpression']);
        $this->setAction(new ChannelFunctionAction($data['actionId'] ?? ChannelFunctionAction::TURN_ON));
        $this->setActionParam($data['actionParam'] ?? null);
        $this->setChannel($data['channel'] ?? null);
        $this->setDateStart(empty($data['dateStart']) ? new \DateTime() : \DateTime::createFromFormat(\DateTime::ATOM, $data['dateStart']));
        $this->setDateEnd(empty($data['dateEnd']) ? null : \DateTime::createFromFormat(\DateTime::ATOM, $data['dateEnd']));
        $this->setMode(new ScheduleMode($data['mode']));
        $this->setCaption($data['caption'] ?? null);
        $this->setRetry($data['retry'] ?? true);
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    public function getTimeExpression(): string {
        return $this->timeExpression;
    }

    public function setTimeExpression(string $timeExpression) {
        $parts = explode(' ', $timeExpression);
        Assert::that($parts[0])->notEq('*')->notEq('*/2')->notEq('*/3')->notEq('*/4');
        $this->timeExpression = $timeExpression;
    }

    /**
     * @return IODeviceChannel
     */
    public function getChannel() {
        return $this->channel;
    }

    /** @param IODeviceChannel|null $channel */
    public function setChannel($channel) {
        $this->channel = $channel;
    }

    public function getAction(): ChannelFunctionAction {
        return new ChannelFunctionAction($this->action);
    }

    public function setAction(ChannelFunctionAction $action) {
        $this->action = $action->getValue();
    }

    /** @return array|null */
    public function getActionParam() {
        return $this->actionParam ? json_decode($this->actionParam, true) : $this->actionParam;
    }

    /** @param array|null */
    public function setActionParam($actionParam) {
        if ($actionParam) {
            $params = json_encode($actionParam);
        } else {
            $params = null;
        }
        $this->actionParam = $params;
    }

    public function getMode(): ScheduleMode {
        return new ScheduleMode($this->mode);
    }

    public function setMode(ScheduleMode $mode) {
        $this->mode = $mode->getValue();
    }

    /**
     * @return mixed
     */
    public function getDateStart() {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     */
    public function setDateStart(\DateTime $dateStart) {
        $this->dateStart = $dateStart;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateEnd() {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime|null $dateEnd
     */
    public function setDateEnd($dateEnd) {
        $this->dateEnd = $dateEnd;
    }

    /**
     * @return mixed
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getNextCalculationDate() {
        return $this->nextCalculationDate;
    }

    public function setNextCalculationDate(\DateTime $nextCalculationDate) {
        $this->nextCalculationDate = $nextCalculationDate;
    }

    /**
     * @return mixed
     */
    public function getCaption() {
        return $this->caption;
    }

    /**
     * @param mixed $caption
     */
    public function setCaption($caption) {
        $this->caption = $caption;
    }

    /** @return \DateTimeZone */
    public function getUserTimezone() {
        return new \DateTimeZone($this->getUser()->getTimezone());
    }

    public function getRetry(): bool {
        return $this->retry;
    }

    public function setRetry(bool $retry) {
        if ($this->channel) {
            $alwaysRetryFunctions = [ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::CONTROLLINGTHEGARAGEDOOR()];
            if (in_array($this->getChannel()->getFunction(), $alwaysRetryFunctions)) {
                $retry = true;
            }
        }
        $this->retry = $retry;
    }
}
