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

namespace App\Controller\Api;

use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceList;
use App\Entity\MeasurementLogs\EnergyTariffPriceListAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceListItem;
use App\Model\ApiVersions;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyTariffController extends RestController {
    public function __construct(private readonly EntityManagerInterface $measurementLogsEntityManager) {
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariffs")
     */
    public function getEnergyTariffsAction(Request $request) {
        $this->ensureApiVersion24($request);
        $tariffs = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariff::class)->findAll();
        return $this->view(array_map(fn(EnergyTariff $tariff) => $this->serializeTariff($tariff), $tariffs));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariffs/{tariffId}/price-lists")
     */
    public function getEnergyTariffPriceListsAction(int $tariffId, Request $request) {
        $this->ensureApiVersion24($request);
        $this->findTariffOrThrow($tariffId);
        $priceLists = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffPriceList::class)->findBy([
            'tariff' => $tariffId,
            'userId' => $this->getCurrentUserOrThrow()->getId(),
        ]);
        return $this->view(array_map(fn(EnergyTariffPriceList $priceList) => $this->serializePriceList($priceList), $priceLists));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/energy-tariffs/{tariffId}/price-lists")
     */
    public function postEnergyTariffPriceListAction(int $tariffId, Request $request) {
        $this->ensureApiVersion24($request);
        $tariff = $this->findTariffOrThrow($tariffId);
        $data = $this->getRequestData($request);
        Assertion::keyExists($data, 'name');

        $priceList = new EnergyTariffPriceList();
        $priceList->setTariff($tariff);
        $priceList->setUserId($this->getCurrentUserOrThrow()->getId());
        $priceList->setName($data['name']);
        $this->synchronizePriceListItems($priceList, $data['items'] ?? []);
        $this->getMeasurementLogsEntityManager()->persist($priceList);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializePriceList($priceList), Response::HTTP_CREATED);
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariffs/{tariffId}/price-lists/{priceListId}")
     */
    public function getEnergyTariffPriceListAction(int $tariffId, int $priceListId, Request $request) {
        $this->ensureApiVersion24($request);
        return $this->view($this->serializePriceList($this->findOwnedPriceListOrThrow($tariffId, $priceListId)));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Put("/energy-tariffs/{tariffId}/price-lists/{priceListId}")
     */
    public function putEnergyTariffPriceListAction(int $tariffId, int $priceListId, Request $request) {
        $this->ensureApiVersion24($request);
        $priceList = $this->findOwnedPriceListOrThrow($tariffId, $priceListId);
        $data = $this->getRequestData($request);
        if (array_key_exists('name', $data)) {
            $priceList->setName($data['name']);
        }
        if (array_key_exists('items', $data)) {
            $this->synchronizePriceListItems($priceList, $data['items']);
        }
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializePriceList($priceList));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Delete("/energy-tariffs/{tariffId}/price-lists/{priceListId}")
     */
    public function deleteEnergyTariffPriceListAction(int $tariffId, int $priceListId, Request $request) {
        $this->ensureApiVersion24($request);
        $priceList = $this->findOwnedPriceListOrThrow($tariffId, $priceListId);
        $this->getMeasurementLogsEntityManager()->remove($priceList);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-tariff-assignments")
     */
    public function getChannelEnergyTariffAssignmentsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $assignments = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffAssignment::class)->findBy(['channelId' => $channel->getId()]);
        return $this->view(array_map(fn(EnergyTariffAssignment $assignment
        ) => $this->serializeTariffAssignment($assignment), $assignments));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Post("/channels/{channel}/energy-tariff-assignments")
     */
    public function postChannelEnergyTariffAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $data = $this->getRequestData($request);
        $assignment = new EnergyTariffAssignment();
        $assignment->setChannelId($channel->getId());
        $assignment->setTariff($this->findTariffOrThrow((int)$data['tariffId']));
        $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        $assignment->setValidTo($this->parseNullableDateTime($data['validTo'] ?? null));
        $this->getMeasurementLogsEntityManager()->persist($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializeTariffAssignment($assignment), Response::HTTP_CREATED);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Put("/channels/{channel}/energy-tariff-assignments/{assignmentId}")
     */
    public function putChannelEnergyTariffAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findTariffAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        $data = $this->getRequestData($request);
        if (array_key_exists('tariffId', $data)) {
            $assignment->setTariff($this->findTariffOrThrow((int)$data['tariffId']));
        }
        if (array_key_exists('validFrom', $data)) {
            $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        }
        if (array_key_exists('validTo', $data)) {
            $assignment->setValidTo($this->parseNullableDateTime($data['validTo']));
        }
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializeTariffAssignment($assignment));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Delete("/channels/{channel}/energy-tariff-assignments/{assignmentId}")
     */
    public function deleteChannelEnergyTariffAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findTariffAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        $this->getMeasurementLogsEntityManager()->remove($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-price-list-assignments")
     */
    public function getChannelEnergyPriceListAssignmentsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $assignments = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffPriceListAssignment::class)->findBy(['channelId' => $channel->getId()]);
        $assignments = array_values(array_filter($assignments, fn(EnergyTariffPriceListAssignment $assignment
        ) => $assignment->getPriceList()->getUserId() === $this->getCurrentUserOrThrow()->getId()));
        return $this->view(array_map(fn(EnergyTariffPriceListAssignment $assignment
        ) => $this->serializePriceListAssignment($assignment), $assignments));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Post("/channels/{channel}/energy-price-list-assignments")
     */
    public function postChannelEnergyPriceListAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $data = $this->getRequestData($request);
        $assignment = new EnergyTariffPriceListAssignment();
        $assignment->setChannelId($channel->getId());
        $assignment->setPriceList($this->findOwnedPriceListByIdOrThrow((int)$data['priceListId']));
        $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        $assignment->setValidTo($this->parseNullableDateTime($data['validTo'] ?? null));
        $this->getMeasurementLogsEntityManager()->persist($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializePriceListAssignment($assignment), Response::HTTP_CREATED);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Put("/channels/{channel}/energy-price-list-assignments/{assignmentId}")
     */
    public function putChannelEnergyPriceListAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findPriceListAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        Assertion::eq($assignment->getPriceList()->getUserId(), $this->getCurrentUserOrThrow()->getId());
        $data = $this->getRequestData($request);
        if (array_key_exists('priceListId', $data)) {
            $assignment->setPriceList($this->findOwnedPriceListByIdOrThrow((int)$data['priceListId']));
        }
        if (array_key_exists('validFrom', $data)) {
            $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        }
        if (array_key_exists('validTo', $data)) {
            $assignment->setValidTo($this->parseNullableDateTime($data['validTo']));
        }
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializePriceListAssignment($assignment));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Delete("/channels/{channel}/energy-price-list-assignments/{assignmentId}")
     */
    public function deleteChannelEnergyPriceListAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findPriceListAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        Assertion::eq($assignment->getPriceList()->getUserId(), $this->getCurrentUserOrThrow()->getId());
        $this->getMeasurementLogsEntityManager()->remove($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    private function ensureApiVersion24(Request $request): void {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
    }

    private function getRequestData(Request $request): array {
        return json_decode($request->getContent(), true) ?: [];
    }

    private function findTariffOrThrow(int $id): EnergyTariff {
        $tariff = $this->getMeasurementLogsEntityManager()->find(EnergyTariff::class, $id);
        if (!$tariff) {
            throw new NotFoundHttpException();
        }
        return $tariff;
    }

    private function findOwnedPriceListOrThrow(int $tariffId, int $priceListId): EnergyTariffPriceList {
        $priceList = $this->findOwnedPriceListByIdOrThrow($priceListId);
        if ((int)$priceList->getTariff()?->getId() !== $tariffId) {
            throw new NotFoundHttpException();
        }
        return $priceList;
    }

    private function findOwnedPriceListByIdOrThrow(int $priceListId): EnergyTariffPriceList {
        $priceList = $this->getMeasurementLogsEntityManager()->find(EnergyTariffPriceList::class, $priceListId);
        if (!$priceList || $priceList->getUserId() !== $this->getCurrentUserOrThrow()->getId()) {
            throw new NotFoundHttpException();
        }
        return $priceList;
    }

    private function findTariffAssignmentForChannelOrThrow(int $channelId, int $assignmentId): EnergyTariffAssignment {
        $assignment = $this->getMeasurementLogsEntityManager()->find(EnergyTariffAssignment::class, $assignmentId);
        if (!$assignment || $assignment->getChannelId() !== $channelId) {
            throw new NotFoundHttpException();
        }
        return $assignment;
    }

    private function findPriceListAssignmentForChannelOrThrow(int $channelId, int $assignmentId): EnergyTariffPriceListAssignment {
        $assignment = $this->getMeasurementLogsEntityManager()->find(EnergyTariffPriceListAssignment::class, $assignmentId);
        if (!$assignment || $assignment->getChannelId() !== $channelId) {
            throw new NotFoundHttpException();
        }
        return $assignment;
    }

    private function synchronizePriceListItems(EnergyTariffPriceList $priceList, array $items): void {
        foreach ($priceList->getItems()->toArray() as $item) {
            $priceList->removeItem($item);
            $this->getMeasurementLogsEntityManager()->remove($item);
        }
        foreach ($items as $itemData) {
            $item = new EnergyTariffPriceListItem();
            $item->setComponentCode($itemData['componentCode']);
            $item->setZoneCode($itemData['zoneCode'] ?? null);
            $item->setAmount((float)$itemData['amount']);
            $item->setUnit($itemData['unit']);
            $item->setCurrency($itemData['currency']);
            $priceList->addItem($item);
        }
    }

    private function parseDateTime(string $dateTime): \DateTime {
        return new \DateTime($dateTime, new \DateTimeZone('UTC'));
    }

    private function parseNullableDateTime(?string $dateTime): ?\DateTime {
        return $dateTime ? $this->parseDateTime($dateTime) : null;
    }

    private function getMeasurementLogsEntityManager() {
        return $this->measurementLogsEntityManager;
    }

    private function serializeTariff(EnergyTariff $tariff): array {
        return [
            'id' => $tariff->getId(),
            'code' => $tariff->getCode(),
            'name' => $tariff->getName(),
            'config' => $tariff->getConfig(),
        ];
    }

    private function serializePriceList(EnergyTariffPriceList $priceList): array {
        return [
            'id' => $priceList->getId(),
            'tariffId' => $priceList->getTariff()?->getId(),
            'userId' => $priceList->getUserId(),
            'name' => $priceList->getName(),
            'items' => array_map(fn(EnergyTariffPriceListItem $item) => [
                'id' => $item->getId(),
                'componentCode' => $item->getComponentCode(),
                'zoneCode' => $item->getZoneCode(),
                'amount' => $item->getAmount(),
                'unit' => $item->getUnit(),
                'currency' => $item->getCurrency(),
            ], $priceList->getItems()->toArray()),
        ];
    }

    private function serializeTariffAssignment(EnergyTariffAssignment $assignment): array {
        return [
            'id' => $assignment->getId(),
            'channelId' => $assignment->getChannelId(),
            'tariffId' => $assignment->getTariff()?->getId(),
            'tariff' => $assignment->getTariff() ? $this->serializeTariff($assignment->getTariff()) : null,
            'validFrom' => $assignment->getValidFrom()->format('Y-m-d H:i:s'),
            'validTo' => $assignment->getValidTo()?->format('Y-m-d H:i:s'),
        ];
    }

    private function serializePriceListAssignment(EnergyTariffPriceListAssignment $assignment): array {
        return [
            'id' => $assignment->getId(),
            'channelId' => $assignment->getChannelId(),
            'priceListId' => $assignment->getPriceList()?->getId(),
            'priceList' => $assignment->getPriceList() ? $this->serializePriceList($assignment->getPriceList()) : null,
            'validFrom' => $assignment->getValidFrom()->format('Y-m-d H:i:s'),
            'validTo' => $assignment->getValidTo()?->format('Y-m-d H:i:s'),
        ];
    }
}
