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

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Enums\ChannelFunctionAction;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * TODO indexes
 * TODO encoder
 * @ORM\Entity
 * @ORM\Table(name="supla_direct_link")
 */
class DirectLink {
    use BelongsToUser;

    const SLUG_LENGTH_MIN = 10;
    const SLUG_LENGTH_MAX = 16;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="slug", type="string", nullable=false, length=255)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="directLinks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="directLinks")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false)
     * @Groups({"channel"})
     */
    private $channel;

    /**
     * @ORM\Column(name="allowed_actions", type="string", nullable=false, length=255)
     * @Groups({"basic"})
     */
    private $allowedActions;

    /**
     * @ORM\Column(name="active_from", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $activeFrom;

    /**
     * @ORM\Column(name="active_to", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $activeTo;

    /**
     * @ORM\Column(name="executions_limit", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $executionsLimit;

    /**
     * @ORM\Column(name="last_used", type="utcdatetime", nullable=true)
     * @Groups({"basic", "flat"})
     */
    private $lastUsed;

    /**
     * @ORM\Column(name="last_ipv4", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $lastIpv4;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    protected $enabled = true;

    public function __construct(IODeviceChannel $channel) {
        $this->channel = $channel;
        $this->user = $channel->getUser();
        $this->setAllowedActions([]);
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCaption(): string {
        return $this->caption ?? '';
    }

    public function setCaption(string $caption) {
        $this->caption = $caption;
    }

    public function getChannel(): IODeviceChannel {
        return $this->channel;
    }

    public function getUser(): User {
        return $this->user;
    }

    /** @return mixed */
    public function getActiveFrom() {
        return $this->activeFrom;
    }

    /**
     * @return mixed
     */
    public function getActiveTo() {
        return $this->activeTo;
    }

    /**
     * @return mixed
     */
    public function getExecutionsLimit() {
        return $this->executionsLimit;
    }

    /**
     * @return mixed
     */
    public function getLastUsed() {
        return $this->lastUsed;
    }

    /**
     * @return mixed
     */
    public function getLastIpv4() {
        return $this->lastIpv4;
    }

    /**
     * @return mixed
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function generateSlug(PasswordEncoderInterface $slugEncoder) {
        Assertion::null($this->slug);
        $slugLength = random_int(self::SLUG_LENGTH_MIN, self::SLUG_LENGTH_MAX);
        do {
            $randomBytes = bin2hex(random_bytes($slugLength * 2));
            $almostSlug = preg_replace('#[1lI0O]#', '', preg_replace('#[^a-zA-Z0-9]#', '', base64_encode($randomBytes)));
        } while (strlen($almostSlug) < $slugLength);
        $slug = str_shuffle(substr($almostSlug, 0, $slugLength));
        $this->slug = $slugEncoder->encodePassword($slug, null);
        return $slug;
    }

    public function isValidSlug(string $slug, PasswordEncoderInterface $slugEncoder): bool {
        return $slugEncoder->isPasswordValid($this->slug, $slug, null);
    }

    /**
     * @param ChannelFunctionAction[] $allowedActions
     */
    public function setAllowedActions(array $allowedActions) {
        $this->allowedActions = json_encode(array_map(function (ChannelFunctionAction $action) {
            return $action->getId();
        }, $allowedActions));
    }

    /**
     * @return ChannelFunctionAction[]
     */
    public function getAllowedActions(): array {
        $actionIds = json_decode($this->allowedActions, true);
        return $actionIds ? array_map(function ($actionId) {
            return new ChannelFunctionAction($actionId);
        }, $actionIds) : [];
    }

    public function canBeUsed(): bool {
        return $this->isEnabled(); // TODO date, qty etc
    }

    public function update(array $data) {
        $this->enabled = $data['enabled'] ?? false;
        $this->setAllowedActions($data['allowedActions'] ?? []);
    }

    public function verifySlug(string $hashedSlug) {
        return strcmp($this->slug, $hashedSlug) === 0;
    }
}
