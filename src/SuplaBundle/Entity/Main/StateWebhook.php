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
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Enums\ChannelFunction;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\StateWebhookRepository")
 * @ORM\Table(name="supla_state_webhooks")
 */
class StateWebhook {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\OAuth\ApiClient")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\User")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     * @Groups({"basic"})
     */
    private $url;

    /**
     * @ORM\Column(name="access_token", type="string", length=255, nullable=false)
     */
    private $accessToken;

    /**
     * @ORM\Column(name="refresh_token", type="string", length=255, nullable=false)
     */
    private $refreshToken;

    /**
     * @ORM\Column(name="expires_at", type="utcdatetime", nullable=false)
     * @Groups({"basic"})
     */
    private $expiresAt;

    /**
     * @ORM\Column(name="functions_ids", type="string", length=1024, nullable=false)
     */
    private $functionsIds;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=1})
     * @Groups({"basic"})
     */
    private $enabled = false;

    public function __construct(?ApiClient $client, User $user) {
        $this->client = $client;
        $this->user = $user;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url) {
        $this->url = $url;
    }

    public function setAccessToken(string $accessToken) {
        $this->accessToken = $accessToken;
    }

    /** @param ChannelFunction[] $functions */
    public function setFunctions(array $functions) {
        Assertion::notEmpty($functions, 'You must subscribe for some functions.');
        $this->functionsIds = implode(',', EntityUtils::mapToIds($functions));
    }

    public function getFunctionsIds() {
        return $this->functionsIds;
    }

    /**
     * @Groups({"basic"})
     */
    public function getFunctions(): array {
        return array_map(function (int $functionId) {
            return (new ChannelFunction($functionId))->getKey();
        }, explode(',', $this->functionsIds));
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }

    public function setRefreshToken(string $refreshToken) {
        $this->refreshToken = $refreshToken;
    }

    public function setExpiresAt(DateTime $expiresAt) {
        $this->expiresAt = $expiresAt;
    }

    public function getExpiresAt(): DateTime {
        return $this->expiresAt;
    }
}
