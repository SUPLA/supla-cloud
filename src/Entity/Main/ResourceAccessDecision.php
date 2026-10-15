<?php
namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * Accepted rows are independent USER_APPROVAL origins; Core projects grants.
 * openPairId is set for Pending/Rejected only (one outstanding decision per pair).
 * @ORM\Entity
 * @ORM\Table(name="supla_resource_access_decision", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="resource_access_open", columns={"open_pair_id"})
 * }, indexes={@ORM\Index(name="IDX_RESOURCE_ACCESS_PAIR", columns={"pair_id"})})
 */
class ResourceAccessDecision {
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private int $id;
    /** @ORM\ManyToOne(targetEntity="ResourceAccessPair") @ORM\JoinColumn(nullable=false, onDelete="CASCADE") */
    private ResourceAccessPair $pair;
    /** @ORM\Column(name="open_pair_id", type="integer", nullable=true) */
    private ?int $openPairId;
    /** @ORM\Column(type="string", length=8) */
    private string $status;
    /** @ORM\Column(type="integer") */
    private int $permissions;
    /** Request provenance only; consent remains independent of delivery mode.
     * @ORM\Column(name="requested_delivery_mode", type="integer", options={"default":1})
     */
    private int $requestedDeliveryMode = 1;
    /** @ORM\Column(type="integer", options={"default":1}) */
    private int $revision = 1;
    /** @ORM\Column(name="requested_at", type="utcdatetime", nullable=true) */
    private ?\DateTime $requestedAt = null;
    /** @ORM\Column(name="last_decision_at", type="utcdatetime", nullable=true) */
    private ?\DateTime $lastDecisionAt = null;
}
