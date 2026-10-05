<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Controller\Api;

use App\Entity\Main\OcppChargingStation;
use App\EventListener\UnavailableInMaintenance;
use App\Model\Ocpp\OcppChargingStationManager;
use App\Model\Ocpp\OcppConfiguration;
use App\Repository\LocationRepository;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OcppChargingStationController extends RestController {
    public function __construct(private EntityManagerInterface $em, private OcppConfiguration $configuration) {
    }

    /**
     * @Rest\Get("/integrations/ocpp")
     * @Security("is_granted('ROLE_IODEVICES_R')")
     */
    public function listAction(): JsonResponse {
        $stations = $this->em->getRepository(OcppChargingStation::class)->createQueryBuilder('s')
            ->join('s.iodevice', 'd')->addSelect('d')
            ->where('s.user = :user')->setParameter('user', $this->getUser())
            ->orderBy('s.id', 'ASC')->getQuery()->getResult();
        $stations = array_filter($stations, fn($s) => $this->isGranted('accessIdContains', $s->getIODevice()));
        return $this->privateResponse([
            'enabled' => $this->configuration->isEnabled(),
            'stations' => array_values(array_map(fn($s) => $this->configuration->describe($s), $stations)),
        ]);
    }

    /**
     * @Rest\Post("/integrations/ocpp")
     * @Security("is_granted('ROLE_IODEVICES_RW') and is_granted('ROLE_CHANNELS_RW')")
     * @UnavailableInMaintenance
     */
    public function createAction(Request $request, LocationRepository $locations, OcppChargingStationManager $manager): JsonResponse {
        $body = $request->toArray();
        Assertion::keyExists($body, 'name');
        Assertion::string($body['name']);
        Assertion::keyExists($body, 'locationId');
        Assertion::integer($body['locationId']);
        $location = $locations->findForUser($this->getUser(), $body['locationId']);
        $this->denyAccessUnlessGranted('accessIdContains', $location);
        return $this->privateResponse($manager->create($this->getUser(), $location, $body['name']), 201);
    }

    /**
     * @Rest\Post("/integrations/ocpp/{id}/authorization-key", requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_IODEVICES_RW') and is_granted('ROLE_CHANNELS_RW')")
     * @UnavailableInMaintenance
     */
    public function regenerateKeyAction(int $id, OcppChargingStationManager $manager): JsonResponse {
        $station = $this->em->getRepository(OcppChargingStation::class)->findOneBy(['id' => $id, 'user' => $this->getUser()]);
        if (!$station) {
            throw $this->createNotFoundException();
        }
        $this->denyAccessUnlessGranted('accessIdContains', $station->getIODevice());
        return $this->privateResponse($manager->regenerateKey($station));
    }

    private function privateResponse(array $data, int $status = 200): JsonResponse {
        return new JsonResponse($data, $status, ['Cache-Control' => 'private, no-store']);
    }
}
