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

namespace SuplaBundle\Entity\Main;

use Assert\Assertion;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\HasSubject;
use SuplaBundle\Entity\HasSubjectTrait;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ScheduleMode;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ScheduleRepository")
 * @ORM\Table(name="supla_schedule", indexes={
 *     @ORM\Index(name="next_calculation_date_idx", columns={"next_calculation_date"}),
 *     @ORM\Index(name="enabled_idx", columns={"enabled"}),
 *     @ORM\Index(name="date_start_idx", columns={"date_start"})
 * })
 */
class Schedule implements HasSubject, ActionableSubject {
    use BelongsToUser;
    use HasSubjectTrait;

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
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="schedules")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Groups({"channel", "iodevice", "location"})
     * @MaxDepth(1)
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannelGroup", inversedBy="schedules")
     * @ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channelGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Scene", inversedBy="schedules")
     * @ORM\JoinColumn(name="scene_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $scene;

    /**
     * @ORM\Column(name="config", type="string", nullable=true, length=2048)
     * @Groups({"basic"})
     */
    private $config;

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
     * @ORM\Column(name="caption", type="string", length=255, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
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
        Assertion::keyIsset($data, 'mode', 'No schedule mode given.');
        $this->setMode(new ScheduleMode($data['mode']));
        if ($data['subject'] ?? null) {
            $this->setSubject($data['subject']);
        }
        $this->setDateStart(empty($data['dateStart']) ? new DateTime() : DateTime::createFromFormat(DateTime::ATOM, $data['dateStart']));
        $this->setDateEnd(empty($data['dateEnd']) ? null : DateTime::createFromFormat(DateTime::ATOM, $data['dateEnd']));
        $this->setCaption($data['caption'] ?? null);
        $this->setRetry($data['retry'] ?? true);
        $this->setConfig($data['config'] ?? null);
    }

    public function getId() {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    /** @param IODeviceChannel|IODeviceChannelGroup|Scene|null $subject */
    public function setSubject($subject) {
        Assertion::notIsInstanceOf($subject, Schedule::class, 'You cannot control schedule with schedule.');
        $this->initializeSubject($subject);
    }

    /**
     * @Groups({"schedule.subject"})
     * @MaxDepth(1)
     */
    public function getSubject(): ?ActionableSubject {
        return $this->getTheSubject();
    }

    /** Exists only for v2.2- compatibility (there was a "channel" serialization group before. */
    public function getChannel() {
        return $this->channel;
    }

    public function isSubjectEnabled(): bool {
        return $this->getSubjectType() != ActionableSubjectType::CHANNEL() || $this->getSubject()->getIoDevice()->getEnabled();
    }

    /** @return array|null */
    public function getConfig() {
        return $this->config ? json_decode($this->config, true) : null;
    }

    public function setConfig($config) {
        $this->config = $config ? json_encode($config) : null;
    }

    public function getMode(): ScheduleMode {
        return new ScheduleMode($this->mode);
    }

    public function setMode(ScheduleMode $mode) {
        $this->mode = $mode->getValue();
    }

    public function getDateStart(): DateTime {
        return $this->dateStart;
    }

    public function setDateStart(DateTime $dateStart) {
        $this->dateStart = $dateStart;
    }

    public function getDateEnd(): ?DateTime {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTime $dateEnd) {
        $this->dateEnd = $dateEnd;
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getNextCalculationDate() {
        return $this->nextCalculationDate;
    }

    public function setNextCalculationDate(DateTime $nextCalculationDate) {
        $this->nextCalculationDate = $nextCalculationDate;
    }

    public function getCaption(): ?string {
        return $this->caption;
    }

    public function setCaption(?string $caption) {
        $this->caption = $caption;
    }

    public function getUserTimezone(): DateTimeZone {
        return new DateTimeZone($this->getUser()->getTimezone());
    }

    public function getRetry(): bool {
        return $this->retry;
    }

    public function setRetry(bool $retry) {
        if ($this->channel) {
            $alwaysRetryFunctions = [ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::CONTROLLINGTHEGARAGEDOOR()];
            if (in_array($this->getSubject()->getFunction(), $alwaysRetryFunctions)) {
                $retry = true;
            }
        } elseif ($this->channelGroup) {
            $retry = false;
        }
        $this->retry = $retry;
    }

    /** @Groups({"basic"}) */
    public function getFunction(): ChannelFunction {
        return ChannelFunction::SCHEDULE();
    }

    /** @Groups({"basic"}) */
    public function getPossibleActions(): array {
        return $this->getFunction()->getDefaultPossibleActions();
    }

    public function buildServerActionCommand(string $command, array $actionParams = []): string {
        throw new \InvalidArgumentException('Schedules does not have any function in supla-server commands.');
    }

    /** @Groups({"basic"}) */
    public function getOwnSubjectType(): string {
        return ActionableSubjectType::SCHEDULE;
    }
}
