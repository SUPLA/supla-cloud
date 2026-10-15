<?php
namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * SERVER authority is the current Cloud database; never a local ChannelNumber.
 * @ORM\Entity
 * @ORM\Table(name="supla_resource_access_pair", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="resource_access_identity", columns={"user_id", "destination_device_id", "source_channel_id"})
 * }, indexes={@ORM\Index(name="IDX_RESOURCE_ACCESS_USER", columns={"user_id"})})
 */
class ResourceAccessPair {
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private int $id;
    /** @ORM\ManyToOne(targetEntity="User") @ORM\JoinColumn(nullable=false, onDelete="CASCADE") */
    private User $user;
    /** @ORM\Column(name="destination_device_id", type="integer") */
    private int $destinationDeviceId;
    /** @ORM\Column(name="source_channel_id", type="integer") */
    private int $sourceChannelId;
    /** @ORM\Column(name="source_device_id", type="integer") */
    private int $sourceDeviceId;
}
