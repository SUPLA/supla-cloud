<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Entity\Main;

use App\Entity\BelongsToUser;
use Assert\Assertion;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_ocpp_charging_station", indexes={
 *     @ORM\Index(name="IDX_OCPP_CHARGING_STATION_USER", columns={"user_id"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UNIQ_OCPP_CHARGING_STATION_DEVICE", columns={"iodevice_id"}),
 *     @ORM\UniqueConstraint(name="UNIQ_OCPP_CHARGE_POINT_IDENTITY", columns={"charge_point_identity"})
 * })
 */
class OcppChargingStation {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="IODevice", inversedBy="ocppChargingStation")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $iodevice;

    /**
     * @ORM\Column(
     *     name="charge_point_identity",
     *     type="string",
     *     length=64,
     *     options={"charset"="ascii", "collation"="ascii_bin"}
     * )
     */
    private $chargePointIdentity;

    /**
     * Lowercase hexadecimal SHA-512 digest of the binary OCPP AuthorizationKey.
     *
     * @ORM\Column(
     *     name="auth_key_hash",
     *     type="string",
     *     length=128,
     *     options={"fixed"=true, "charset"="ascii", "collation"="ascii_bin"}
     * )
     */
    private $authKeyHash;

    /** @ORM\Column(name="created_at", type="utcdatetime") */
    private $createdAt;

    public function __construct(
        User $user,
        IODevice $iodevice,
        string $chargePointIdentity,
        string $authKeyHash
    ) {
        $this->user = $user;
        $this->iodevice = $iodevice;
        $this->setChargePointIdentity($chargePointIdentity);
        $this->setAuthKeyHash($authKeyHash);
        $this->createdAt = new DateTime();
        $iodevice->setOcppChargingStation($this);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getIODevice(): IODevice {
        return $this->iodevice;
    }

    public function getChargePointIdentity(): string {
        return $this->chargePointIdentity;
    }

    public function setChargePointIdentity(string $chargePointIdentity): void {
        Assertion::regex(
            $chargePointIdentity,
            '/\A[A-Za-z0-9._~-]{1,64}\z/D',
            'Charge Point identity may contain only URL-safe ASCII characters and must not exceed 64 characters.'
        );
        $this->chargePointIdentity = $chargePointIdentity;
    }

    /** @Ignore */
    public function getAuthKeyHash(): string {
        return $this->authKeyHash;
    }

    public function setAuthKeyHash(string $authKeyHash): void {
        Assertion::regex($authKeyHash, '/\A[0-9a-fA-F]{128}\z/D', 'Invalid SHA-512 authentication key hash.');
        $this->authKeyHash = strtolower($authKeyHash);
    }

    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }
}
