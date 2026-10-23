<?php
namespace App\Controller\Api;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Exception\ApiException;
use App\Model\ResourceAccess\ServerResourcePermissions;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ServerResourcePermissionsController extends RestController {
    public function __construct(private ServerResourcePermissions $permissions, private EntityManagerInterface $entityManager) {
    }

    /**
     * @Rest\Get("/resource-access/permissions")
     * @Security("is_granted('ROLE_ACCOUNT_R') and is_granted('ROLE_IODEVICES_R') and is_granted('ROLE_CHANNELS_R')")
     */
    public function listAction(Request $request): JsonResponse {
        $filters = [];
        foreach (['sourceDeviceId', 'destinationDeviceId', 'deviceId', 'permission', 'limit'] as $key) {
            if ($request->query->has($key)) {
                $value = $request->query->get($key);
                ApiException::throwIf(
                    !is_string($value) || !ctype_digit($value) || strlen($value) > 10 || (int)$value < 1,
                    'Invalid permissions filter.'
                );
                $filters[$key] = (int)$value;
            }
        }
        ApiException::throwIf(isset($filters['permission']) && !in_array($filters['permission'], [1, 2, 4], true), 'Invalid permission.');
        ApiException::throwIf(($filters['limit'] ?? 25) > 50, 'Invalid page size.');
        if ($request->query->has('origin')) {
            $filters['origin'] = $request->query->get('origin');
            ApiException::throwIf(!in_array($filters['origin'], ['configuration', 'user-approved', 'other'], true), 'Invalid origin.');
        }
        if ($request->query->has('cursor')) {
            $filters['cursor'] = $this->cursor($request->query->get('cursor'), PHP_INT_MAX, true);
        }
        return new JsonResponse($this->permissions->list($this->getUser()->getId(), $filters, $this->authorizeResource(...)));
    }

    /**
     * @Rest\Get("/resource-access/permissions/{association}/{type}/{resource}", requirements={"association"="^logical-\d+$", "type"="1|2", "resource"="^\d+$"})
     * @Security("is_granted('ROLE_ACCOUNT_R') and is_granted('ROLE_IODEVICES_R') and is_granted('ROLE_CHANNELS_R')")
     */
    public function detailAction(string $association, int $type, int $resource, Request $request): JsonResponse {
        ApiException::throwIf(strlen($association) > 20 || $resource < 1, 'Invalid resource.');
        $cursor = $request->query->has('cursor') ? $this->cursor($request->query->get('cursor'), PHP_INT_MAX) : null;
        $entry = $this->permissions->detail($this->getUser()->getId(), $association, $type, $resource, $this->authorizeResource(...), $cursor);
        if (!$entry) {
            throw $this->createNotFoundException();
        }
        return new JsonResponse($entry);
    }

    private function cursor(mixed $value, int $maxSecond, bool $read = false): string {
        $pattern = $read ? '/^logical-[0-9]{1,20}:[0-9]{1,20}$/D' : '/^[0-9]{1,20}:[0-9]{1,20}$/D';
        ApiException::throwIf(!is_string($value) || !preg_match($pattern, $value), 'Invalid permissions cursor.');
        [, $second] = explode(':', $value);
        ApiException::throwIf((int)$second > $maxSecond, 'Invalid permissions cursor.');
        return $value;
    }

    private function authorizeResource(array $row): bool {
        $subjects = [[IODevice::class, $row['source_device_id']], [IODevice::class, $row['destination_device_id']]];
        if ((int)$row['resource_type'] === 1) {
            $subjects[] = [IODeviceChannel::class, $row['resource_id']];
        }
        foreach ($subjects as [$class, $id]) {
            $subject = $this->entityManager->find($class, $id);
            if (!$subject || !$subject->belongsToUser($this->getUser()) || !$this->isGranted('accessIdContains', $subject)) {
                return false;
            }
        }
        return true;
    }
}
