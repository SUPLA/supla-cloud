<?php
namespace App\Controller\Api;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Model\ResourceAccess\ResourceAccessDecisions;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ResourceAccessController extends RestController {
    public function __construct(private ResourceAccessDecisions $decisions, private EntityManagerInterface $entityManager) {
    }

    /**
     * @Rest\Get("/resource-access")
     * @Security("is_granted('ROLE_ACCOUNT_R') and is_granted('ROLE_IODEVICES_R') and is_granted('ROLE_CHANNELS_R')")
     */
    public function listAction(Request $request): JsonResponse {
        $destination = $request->query->get('destinationDeviceId');
        $device = $request->query->get('deviceId');
        $before = $request->query->get('beforeId');
        $limit = $request->query->get('limit', '50');
        $status = $request->query->get('status');
        \App\Exception\ApiException::throwIf($status !== null && $status !== 'PENDING', 'Invalid request status filter.');
        foreach ([$destination, $device, $before, $limit] as $filter) {
            if ($filter !== null && (!ctype_digit((string)$filter) || (int)$filter < 1)) {
                throw $this->createNotFoundException();
            }
        }
        \App\Exception\ApiException::throwIf((int)$limit > 50, 'Invalid page size.');
        $page = $this->decisions->list(
            $this->getUser()->getId(),
            $this->authorizePair(...),
            $destination !== null ? (int)$destination : null,
            $device !== null ? (int)$device : null,
            $before !== null ? (int)$before : null,
            (int)$limit,
            $status
        );
        return new JsonResponse(
            array_map($this->present(...), $page['items']),
            200,
            $page['nextCursor'] !== null ? ['X-Next-Cursor' => $page['nextCursor']] : []
        );
    }

    /**
     * @Rest\Get("/resource-access/{id}", requirements={"id"="^\d+$"})
     * @Security("is_granted('ROLE_ACCOUNT_R') and is_granted('ROLE_IODEVICES_R') and is_granted('ROLE_CHANNELS_R')")
     */
    public function detailAction(int $id): JsonResponse {
        $entry = $this->decisions->detail($this->getUser()->getId(), $id, $this->authorizePair(...));
        if ($entry !== null) {
            return new JsonResponse($this->present($entry));
        }
        throw $this->createNotFoundException();
    }

    /**
     * @Rest\Post("/resource-access/{id}/{action}", requirements={"id"="^\d+$", "action"="approve|reject|remove"})
     * @Security("is_granted('ROLE_ACCOUNT_RW') and is_granted('ROLE_IODEVICES_RW') and is_granted('ROLE_CHANNELS_RW')")
     */
    public function changeAction(int $id, string $action, Request $request): JsonResponse {
        $snapshot = $request->toArray();
        \App\Exception\ApiException::throwIf(
            count($snapshot) !== 3 || array_diff(array_keys($snapshot), ['revision', 'permissions', 'status']),
            'Invalid access decision snapshot.'
        );
        $this->decisions->change($this->getUser()->getId(), $id, $snapshot, $action, $this->authorizePair(...));
        return new JsonResponse(null, 204);
    }

    private function present(array $entry): array {
        $entry['canManage'] = $this->isGranted('ROLE_ACCOUNT_RW') && $this->isGranted('ROLE_IODEVICES_RW')
            && $this->isGranted('ROLE_CHANNELS_RW');
        $entry['canApprove'] = $entry['canApprove'] && $entry['canManage'];
        return $entry;
    }

    private function authorizePair(array $row, array $context): bool {
        $subjects = [
            IODevice::class => [$row['destination_device_id'], $row['source_device_id']],
            IODeviceChannel::class => [$row['source_channel_id']],
        ];
        foreach ($subjects as $class => $ids) {
            foreach ($ids as $id) {
                $subject = $this->entityManager->find($class, $id);
                // Allow the owner to remove an orphan decision; never expose a new owner's metadata.
                if ($subject && $subject->belongsToUser($this->getUser()) && !$this->isGranted('accessIdContains', $subject)) {
                    return false;
                }
            }
        }
        return true;
    }
}
