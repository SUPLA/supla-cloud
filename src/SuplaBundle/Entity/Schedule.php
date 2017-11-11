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
use SuplaBundle\Enums\ScheduleAction;
use SuplaBundle\Enums\ScheduleMode;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_schedule", indexes={
 *     @ORM\Index(name="next_calculation_date_idx", columns={"next_calculation_date"}),
 *     @ORM\Index(name="enabled_idx", columns={"enabled"}),
 *     @ORM\Index(name="date_start_idx", columns={"date_start"})
 * })
 */
class Schedule {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="schedules")
     * @Constraints\NotNull
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="time_expression", type="string", length=100, nullable=false)
     * @Constraints\Length(max=100)
     * @Groups({"basic"})
     */
    private $timeExpression;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="schedules")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false)
     * @Constraints\NotNull
     * @Groups({"channel", "iodevice", "location"})
     */
    private $channel;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     * @Constraints\NotNull
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
     * @Constraints\NotNull()
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
    protected $enabled = true;

    /**
     * @ORM\Column(name="next_calculation_date", type="utcdatetime", nullable=true)
     */
    private $nextCalculationDate;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     * @Constraints\Length(max=255)
     * @Groups({"basic"})
     */
    private $caption;

    public function __construct(User $user = null, array $data = []) {
        $this->user = $user;
        if (count($data)) {
            $this->fill($data);
        }
    }

    public function fill(array $data) {
        Assert::that($data)->notEmptyKey('timeExpression');
        $this->setTimeExpression($data['timeExpression']);
        $this->setAction(new ScheduleAction($data['action'] ?? ScheduleAction::TURN_ON));
        $this->setActionParam($data['actionParam'] ?? null);
        $this->setChannel($data['channel'] ?? null);
        $this->setDateStart(empty($data['dateStart']) ? new \DateTime() : \DateTime::createFromFormat(\DateTime::ATOM, $data['dateStart']));
        $this->setDateEnd(empty($data['dateEnd']) ? null : \DateTime::createFromFormat(\DateTime::ATOM, $data['dateEnd']));
        $this->setMode(new ScheduleMode($data['scheduleMode']));
        $this->setCaption($data['caption'] ?? null);
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

    /**
     * @return mixed
     */
    public function getTimeExpression() {
        return $this->timeExpression;
    }

    /**
     * @param mixed $timeExpression
     */
    public function setTimeExpression($timeExpression) {
        $this->timeExpression = $timeExpression;
    }

    /**
     * @return IODeviceChannel
     */
    public function getChannel() {
        return $this->channel;
    }

    public function setChannel($channel) {
        $this->channel = $channel;
    }

    public function getAction(): ScheduleAction {
        return new ScheduleAction($this->action);
    }

    public function setAction(ScheduleAction $action) {
        $this->action = $action->getValue();
    }

    /**
     * @return string
     */
    public function getActionParam() {
        return $this->actionParam;
    }

    /** @param array|null */
    public function setActionParam($actionParam) {
        $params = $this->getAction()->validateActionParam($actionParam);
        if ($params) {
            $params = json_encode($params);
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
     * @return mixed
     */
    public function getDateEnd() {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
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
}
