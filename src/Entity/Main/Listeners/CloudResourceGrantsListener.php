<?php
namespace App\Entity\Main\Listeners;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Model\ResourceAccess\CloudResourceGrants;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Common\EventSubscriber;

/** Doctrine postUpdate/postPersist/postRemove run INSIDE UnitOfWork's write transaction, before commit. */
class CloudResourceGrantsListener implements EventSubscriber {
    private array $deletions = [];

    public function getSubscribedEvents(): array {
        return ['postUpdate', 'postPersist', 'preRemove', 'postRemove'];
    }

    public function postPersist(LifecycleEventArgs $args): void {
        $this->changed($args, true);
    }

    public function postUpdate(PostUpdateEventArgs $args): void {
        $this->changed($args, false);
    }

    private function changed(LifecycleEventArgs $args, bool $new): void {
        $entity = $args->getObject();
        if (!$entity instanceof IODeviceChannel && !$entity instanceof IODevice) {
            return;
        }
        $grants = new CloudResourceGrants($args->getObjectManager()->getConnection());
        $changes = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);
        if (!$new && isset($changes['user']) && $changes['user'][0]) {
            $grants->metadata(
                $changes['user'][0]->getId(),
                $entity instanceof IODeviceChannel ? $entity->getIoDevice()->getId() : $entity->getId(),
                $entity instanceof IODeviceChannel ? $entity->getId() : null
            );
        }
        if ($entity instanceof IODeviceChannel) {
            if ($new || isset($changes['userConfig'])) {
                $grants->configuration($entity->getId(), $new ? null : (json_decode($changes['userConfig'][0] ?: '{}', true) ?: []));
                if (!$new) {
                    $grants->metadata($entity->getUser()->getId(), $entity->getIoDevice()->getId(), $entity->getId());
                }
            }
            if (array_intersect(['function', 'type', 'properties', 'user', 'iodevice'], array_keys($changes))) {
                $grants->metadata($entity->getUser()->getId(), $entity->getIoDevice()->getId(), $entity->getId());
            }
        } elseif (array_intersect(['flags', 'protocolVersion', 'isVirtual', 'user'], array_keys($changes))) {
            $grants->metadata($entity->getUser()->getId(), $entity->getId());
            $grants->projectDevice($entity->getUser()->getId(), $entity->getId());
        }
    }

    public function preRemove(LifecycleEventArgs $args): void {
        $entity = $args->getObject();
        if ($entity instanceof IODeviceChannel || $entity instanceof IODevice) {
            $this->deletions[spl_object_id($entity)] = [$entity->getUser()->getId(),
                $entity instanceof IODeviceChannel ? $entity->getIoDevice()->getId() : $entity->getId(), $entity->getId()];
        }
    }

    public function postRemove(LifecycleEventArgs $args): void {
        $entity = $args->getObject();
        if (!$entity instanceof IODeviceChannel && !$entity instanceof IODevice) {
            return;
        }
        [$user, $device, $channel] = $this->deletions[spl_object_id($entity)];
        unset($this->deletions[spl_object_id($entity)]);
        // Capture identity in preRemove without writing; postRemove is inside the flush transaction.
        $connection = $args->getObjectManager()->getConnection();
        $grants = new CloudResourceGrants($connection);
        if ($entity instanceof IODeviceChannel) {
            $grants->deletedChannel($user, $device, $channel);
        } else {
            $result = $connection->executeQuery('CALL supla_suplan_cleanup(?,?)', [$user, $device]);
            $result->free();
        }
    }
}
