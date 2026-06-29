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
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Enums\BillingPeriodUnit;
use App\Enums\ChannelFunction;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
use App\Model\ApiVersions;
use App\Model\MeasurementLogs\EnergyCostLogHydrator;
use App\Model\MeasurementLogs\EnergyCostRowFetcher;
use App\Model\MeasurementLogs\EnergyCostSummaryBuilder;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyTariffController extends RestController {
    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly EnergyCostRowFetcher $energyCostRowFetcher,
        private readonly EnergyCostLogHydrator $energyCostLogHydrator,
        private readonly EnergyCostSummaryBuilder $energyCostSummaryBuilder,
    ) {
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
     * @Rest\Get("/energy-tariff-profiles")
     */
    public function getEnergyTariffProfilesAction(Request $request) {
        $this->ensureApiVersion24($request);
        $profiles = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffProfile::class)->findBy([
            'userId' => $this->getCurrentUserOrThrow()->getId(),
        ], ['id' => 'ASC']);
        return $this->view(array_map(fn(EnergyTariffProfile $profile) => $this->serializeProfile($profile), $profiles));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/energy-tariff-profiles")
     */
    public function postEnergyTariffProfileAction(Request $request) {
        $this->ensureApiVersion24($request);
        $data = $this->getRequestData($request);
        Assertion::keyExists($data, 'name');
        Assertion::keyExists($data, 'tariffPeriods');

        $profile = new EnergyTariffProfile();
        $profile->setUserId($this->getCurrentUserOrThrow()->getId());
        $profile->setName($data['name']);
        $this->synchronizeProfileTariffPeriods($profile, $data['tariffPeriods']);
        $this->validateProfile($profile);

        $this->getMeasurementLogsEntityManager()->persist($profile);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializeProfile($profile), Response::HTTP_CREATED);
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariff-profiles/{profileId}")
     */
    public function getEnergyTariffProfileAction(int $profileId, Request $request) {
        $this->ensureApiVersion24($request);
        return $this->view($this->serializeProfile($this->findOwnedProfileOrThrow($profileId)));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Put("/energy-tariff-profiles/{profileId}")
     */
    public function putEnergyTariffProfileAction(int $profileId, Request $request) {
        $this->ensureApiVersion24($request);
        $profile = $this->findOwnedProfileOrThrow($profileId);
        $data = $this->getRequestData($request);
        if (array_key_exists('name', $data)) {
            $profile->setName($data['name']);
        }
        if (array_key_exists('tariffPeriods', $data)) {
            $this->synchronizeProfileTariffPeriods($profile, $data['tariffPeriods']);
        }
        $this->validateProfile($profile);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializeProfile($profile));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Delete("/energy-tariff-profiles/{profileId}")
     */
    public function deleteEnergyTariffProfileAction(int $profileId, Request $request) {
        $this->ensureApiVersion24($request);
        $profile = $this->findOwnedProfileOrThrow($profileId);
        $this->getMeasurementLogsEntityManager()->remove($profile);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-tariff-profile-assignment")
     */
    public function getChannelEnergyTariffProfileAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $assignment = $this->findProfileAssignmentForChannel($channel->getId());
        return $this->view($assignment ? $this->serializeProfileAssignment($assignment) : null);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Put("/channels/{channel}/energy-tariff-profile-assignment")
     */
    public function putChannelEnergyTariffProfileAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $data = $this->getRequestData($request);
        Assertion::keyExists($data, 'profileId');

        $assignment = $this->findProfileAssignmentForChannel($channel->getId()) ?? new EnergyTariffProfileAssignment($channel->getId());
        $assignment->setProfile($this->findOwnedProfileOrThrow((int)$data['profileId']));
        $this->getMeasurementLogsEntityManager()->persist($assignment);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializeProfileAssignment($assignment));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Delete("/channels/{channel}/energy-tariff-profile-assignment")
     */
    public function deleteChannelEnergyTariffProfileAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $assignment = $this->findProfileAssignmentForChannel($channel->getId());
        if ($assignment) {
            $this->getMeasurementLogsEntityManager()->remove($assignment);
            $this->getMeasurementLogsEntityManager()->flush();
        }
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-cost-logs")
     */
    public function getChannelEnergyCostLogsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $rows = $this->fetchElectricityCostRows(
            $channel->getId(),
            (int)$request->query->get('afterTimestamp'),
            (int)$request->query->get('beforeTimestamp'),
            $request->query->get('order') !== 'ASC',
            (int)$request->query->get('limit', $this->energyCostRowFetcher->getRecordLimitPerRequest()),
            (int)$request->query->get('offset', 0)
        );

        return $this->view($this->energyCostLogHydrator->hydrateLogs($rows));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-cost-summaries")
     */
    public function getChannelEnergyCostSummariesAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $afterTimestamp = (int)$request->query->get('afterTimestamp');
        $beforeTimestamp = (int)$request->query->get('beforeTimestamp');

        return $this->view(
            $this->energyCostSummaryBuilder->buildSummaries(
                $channel->getId(),
                $afterTimestamp,
                $beforeTimestamp,
                $this->energyCostRowFetcher,
            )
        );
    }

    private function ensureElectricityMeterChannel(IODeviceChannel $channel): void {
        Assertion::eq($channel->getFunction()->getId(), ChannelFunction::ELECTRICITYMETER, 'Unsupported channel function.');
    }

    private function fetchElectricityCostRows(
        int $channelId,
        int $afterTimestamp,
        int $beforeTimestamp,
        bool $orderDesc,
        int $limit,
        int $offset
    ): array {
        return $this->energyCostRowFetcher->fetchCostRows($channelId, $afterTimestamp, $beforeTimestamp, $orderDesc, $limit, $offset);
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

    private function findOwnedProfileOrThrow(int $profileId): EnergyTariffProfile {
        $profile = $this->getMeasurementLogsEntityManager()->find(EnergyTariffProfile::class, $profileId);
        if (!$profile || $profile->getUserId() !== $this->getCurrentUserOrThrow()->getId()) {
            throw new NotFoundHttpException();
        }
        return $profile;
    }

    private function findProfileAssignmentForChannel(int $channelId): ?EnergyTariffProfileAssignment {
        return $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffProfileAssignment::class)->findOneBy([
            'channelId' => $channelId,
        ]);
    }

    private function synchronizeProfileTariffPeriods(EnergyTariffProfile $profile, array $tariffPeriods): void {
        Assertion::isArray($tariffPeriods);
        foreach ($profile->getTariffPeriods()->toArray() as $tariffPeriod) {
            $profile->removeTariffPeriod($tariffPeriod);
            $this->getMeasurementLogsEntityManager()->remove($tariffPeriod);
        }

        foreach ($tariffPeriods as $tariffPeriodData) {
            Assertion::keyExists($tariffPeriodData, 'tariffId');
            Assertion::keyExists($tariffPeriodData, 'pricePeriods');

            $tariffPeriod = new EnergyTariffProfileTariffPeriod();
            $tariffPeriod->setTariff($this->findTariffOrThrow((int)$tariffPeriodData['tariffId']));
            $tariffPeriod->setValidFrom($this->parseNullableDateTime($tariffPeriodData['validFrom'] ?? null));
            $tariffPeriod->setValidTo($this->parseNullableDateTime($tariffPeriodData['validTo'] ?? null));
            $this->synchronizePricePeriods($tariffPeriod, $tariffPeriodData['pricePeriods']);
            $profile->addTariffPeriod($tariffPeriod);
        }
    }

    private function synchronizePricePeriods(EnergyTariffProfileTariffPeriod $tariffPeriod, array $pricePeriods): void {
        Assertion::isArray($pricePeriods);
        foreach ($tariffPeriod->getPricePeriods()->toArray() as $pricePeriod) {
            $tariffPeriod->removePricePeriod($pricePeriod);
            $this->getMeasurementLogsEntityManager()->remove($pricePeriod);
        }

        foreach ($pricePeriods as $pricePeriodData) {
            Assertion::keyExists($pricePeriodData, 'billingPeriodLength');
            Assertion::keyExists($pricePeriodData, 'billingPeriodUnit');
            Assertion::keyExists($pricePeriodData, 'currency');
            Assertion::keyExists($pricePeriodData, 'items');

            $pricePeriod = new EnergyTariffProfilePricePeriod();
            $pricePeriod->setBillingPeriodLength((int)$pricePeriodData['billingPeriodLength']);
            $pricePeriod->setBillingPeriodUnit($this->parseBillingPeriodUnit($pricePeriodData['billingPeriodUnit']));
            $pricePeriod->setCurrency($pricePeriodData['currency']);
            $pricePeriod->setValidFrom($this->parseNullableDateTime($pricePeriodData['validFrom'] ?? null));
            $pricePeriod->setValidTo($this->parseNullableDateTime($pricePeriodData['validTo'] ?? null));
            $this->synchronizePriceItems($pricePeriod, $pricePeriodData['items']);
            $tariffPeriod->addPricePeriod($pricePeriod);
        }
    }

    private function synchronizePriceItems(EnergyTariffProfilePricePeriod $pricePeriod, array $items): void {
        Assertion::isArray($items);
        foreach ($pricePeriod->getItems()->toArray() as $item) {
            $pricePeriod->removeItem($item);
            $this->getMeasurementLogsEntityManager()->remove($item);
        }

        foreach ($items as $itemData) {
            Assertion::keyExists($itemData, 'componentCode');
            Assertion::keyExists($itemData, 'amount');
            Assertion::keyExists($itemData, 'unit');
            $item = new EnergyTariffProfilePriceItem();
            $item->setComponentCode($this->parseEnergyPriceComponent($itemData['componentCode']));
            $item->setZoneCode($itemData['zoneCode'] ?? null);
            $item->setAmount((float)$itemData['amount']);
            $item->setUnit($this->parseEnergyPriceUnit($itemData['unit']));
            $pricePeriod->addItem($item);
        }
    }

    private function validateProfile(EnergyTariffProfile $profile): void {
        Assertion::notBlank($profile->getName());
        $tariffPeriods = $profile->getTariffPeriods()->toArray();
        Assertion::notEmpty($tariffPeriods);

        usort($tariffPeriods, fn(
            EnergyTariffProfileTariffPeriod $left,
            EnergyTariffProfileTariffPeriod $right
        ) => $this->compareDateTimeStartNullable($left->getValidFrom(), $right->getValidFrom()));
        $previousTariffPeriod = null;
        foreach ($tariffPeriods as $tariffPeriod) {
            $this->assertEndAfterStart($tariffPeriod->getValidFrom(), $tariffPeriod->getValidTo());
            if ($previousTariffPeriod) {
                Assertion::true(
                    $this->compareEndToStart($previousTariffPeriod->getValidTo(), $tariffPeriod->getValidFrom()) <= 0,
                    'Tariff periods cannot overlap.'
                );
            }
            $this->validatePricePeriodsCoverage($tariffPeriod);
            $previousTariffPeriod = $tariffPeriod;
        }
    }

    private function validatePricePeriodsCoverage(EnergyTariffProfileTariffPeriod $tariffPeriod): void {
        $pricePeriods = $tariffPeriod->getPricePeriods()->toArray();
        Assertion::notEmpty($pricePeriods, 'Tariff period must contain at least one price period.');
        usort($pricePeriods, fn(
            EnergyTariffProfilePricePeriod $left,
            EnergyTariffProfilePricePeriod $right
        ) => $this->compareDateTimeStartNullable($left->getValidFrom(), $right->getValidFrom()));

        $zoneCodes = array_map(fn(array $zone) => $zone['code'], $tariffPeriod->getTariff()?->getConfig()['zones'] ?? []);
        $previousPricePeriod = null;
        foreach ($pricePeriods as $index => $pricePeriod) {
            Assertion::greaterThan($pricePeriod->getBillingPeriodLength(), 0);
            Assertion::regex($pricePeriod->getCurrency(), '/^[A-Z]{3}$/');
            $this->assertEndAfterStart($pricePeriod->getValidFrom(), $pricePeriod->getValidTo());
            if ($tariffPeriod->getValidFrom() && $pricePeriod->getValidFrom()) {
                Assertion::greaterOrEqualThan(
                    $pricePeriod->getValidFrom()->getTimestamp(),
                    $tariffPeriod->getValidFrom()->getTimestamp(),
                    'Price period cannot start before the tariff period.'
                );
            }
            if ($tariffPeriod->getValidTo()) {
                Assertion::notNull($pricePeriod->getValidTo(), 'Price periods must not exceed the tariff period.');
                Assertion::lessOrEqualThan(
                    $pricePeriod->getValidTo()->getTimestamp(),
                    $tariffPeriod->getValidTo()->getTimestamp(),
                    'Price period cannot end after the tariff period.'
                );
            }
            if ($index === 0) {
                Assertion::eq(
                    $this->compareDateTimeStartNullable($pricePeriod->getValidFrom(), $tariffPeriod->getValidFrom()),
                    0,
                    'Price periods must cover the full tariff period.'
                );
            }
            if ($previousPricePeriod) {
                Assertion::eq(
                    $this->compareEndToStart($previousPricePeriod->getValidTo(), $pricePeriod->getValidFrom()),
                    0,
                    'Price periods cannot overlap and must cover the full tariff period without gaps.'
                );
            }
            $items = $pricePeriod->getItems()->toArray();
            Assertion::notEmpty($items, 'Price period must contain at least one price item.');
            foreach ($items as $item) {
                Assertion::true($item->getComponentCode()->supportsUnit($item->getUnit()), 'Price item unit is not allowed for selected component.');
                if ($item->getZoneCode() !== null && $zoneCodes) {
                    Assertion::inArray($item->getZoneCode(), $zoneCodes, 'Price item zone must exist in selected tariff.');
                }
            }
            $previousPricePeriod = $pricePeriod;
        }

        Assertion::eq(
            $this->compareDateTimeNullable(end($pricePeriods)->getValidTo(), $tariffPeriod->getValidTo()),
            0,
            'Price periods must cover the full tariff period.'
        );
    }

    private function assertEndAfterStart(?\DateTime $validFrom, ?\DateTime $validTo): void {
        if ($validFrom && $validTo) {
            Assertion::greaterThan($validTo->getTimestamp(), $validFrom->getTimestamp(), 'Period end must be later than period start.');
        }
    }

    private function compareDateTimeStartNullable(?\DateTime $left, ?\DateTime $right): int {
        if ($left === null && $right === null) {
            return 0;
        }
        if ($left === null) {
            return -1;
        }
        if ($right === null) {
            return 1;
        }
        return $left <=> $right;
    }

    private function compareEndToStart(?\DateTime $left, ?\DateTime $right): int {
        if ($left === null && $right === null) {
            return 1;
        }
        if ($left === null) {
            return 1;
        }
        if ($right === null) {
            return 1;
        }
        return $left <=> $right;
    }

    private function compareDateTimeNullable(?\DateTime $left, ?\DateTime $right): int {
        if ($left === null && $right === null) {
            return 0;
        }
        if ($left === null) {
            return 1;
        }
        if ($right === null) {
            return -1;
        }
        return $left <=> $right;
    }

    private function maxDateTime(\DateTime $base, ?\DateTime ...$candidates): \DateTime {
        $max = clone $base;
        foreach ($candidates as $candidate) {
            if ($candidate && $candidate > $max) {
                $max = clone $candidate;
            }
        }
        return $max;
    }

    private function minDateTime(\DateTime $base, ?\DateTime ...$candidates): \DateTime {
        $min = clone $base;
        foreach ($candidates as $candidate) {
            if ($candidate && $candidate < $min) {
                $min = clone $candidate;
            }
        }
        return $min;
    }

    private function parseDateTime(string $dateTime): \DateTime {
        return new \DateTime($dateTime, new \DateTimeZone('UTC'));
    }

    private function parseNullableDateTime(?string $dateTime): ?\DateTime {
        return $dateTime ? $this->parseDateTime($dateTime) : null;
    }

    /**
     * Accept enum case names for API stability and integer values for internal compatibility.
     */
    private function parseEnergyPriceComponent(string|int $componentCode): EnergyPriceComponent {
        if (is_int($componentCode) || ctype_digit((string)$componentCode)) {
            return EnergyPriceComponent::from((int)$componentCode);
        }

        $constantName = EnergyPriceComponent::class . '::' . $componentCode;
        $component = defined($constantName) ? constant($constantName) : null;
        Assertion::notNull($component, 'Invalid energy price component.');
        return $component;
    }

    private function parseEnergyPriceUnit(string $unit): EnergyPriceUnit {
        $parsed = EnergyPriceUnit::tryFrom($unit);
        Assertion::notNull($parsed, 'Invalid energy price unit.');
        return $parsed;
    }

    private function parseBillingPeriodUnit(string $unit): BillingPeriodUnit {
        $parsed = BillingPeriodUnit::tryFrom($unit);
        Assertion::notNull($parsed, 'Invalid billing period unit.');
        return $parsed;
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

    private function serializeProfile(EnergyTariffProfile $profile): array {
        return [
            'id' => $profile->getId(),
            'userId' => $profile->getUserId(),
            'name' => $profile->getName(),
            'tariffPeriods' => array_map(fn(EnergyTariffProfileTariffPeriod $tariffPeriod
            ) => $this->serializeTariffPeriod($tariffPeriod), $profile->getTariffPeriods()->toArray()),
        ];
    }

    private function serializeTariffPeriod(EnergyTariffProfileTariffPeriod $tariffPeriod): array {
        return [
            'id' => $tariffPeriod->getId(),
            'tariffId' => $tariffPeriod->getTariff()?->getId(),
            'tariff' => $tariffPeriod->getTariff() ? $this->serializeTariff($tariffPeriod->getTariff()) : null,
            'validFrom' => $tariffPeriod->getValidFrom()?->format(\DateTime::ATOM),
            'validTo' => $tariffPeriod->getValidTo()?->format(\DateTime::ATOM),
            'pricePeriods' => array_map(fn(EnergyTariffProfilePricePeriod $pricePeriod
            ) => $this->serializePricePeriod($pricePeriod), $tariffPeriod->getPricePeriods()->toArray()),
        ];
    }

    private function serializePricePeriod(EnergyTariffProfilePricePeriod $pricePeriod): array {
        return [
            'id' => $pricePeriod->getId(),
            'billingPeriodLength' => $pricePeriod->getBillingPeriodLength(),
            'billingPeriodUnit' => $pricePeriod->getBillingPeriodUnit()->value,
            'currency' => $pricePeriod->getCurrency(),
            'validFrom' => $pricePeriod->getValidFrom()?->format(\DateTime::ATOM),
            'validTo' => $pricePeriod->getValidTo()?->format(\DateTime::ATOM),
            'items' => array_map(fn(EnergyTariffProfilePriceItem $item) => [
                'id' => $item->getId(),
                'componentCode' => $item->getComponentCode()->name,
                'zoneCode' => $item->getZoneCode(),
                'amount' => $item->getAmount(),
                'unit' => $item->getUnit()->value,
            ], $pricePeriod->getItems()->toArray()),
        ];
    }

    private function serializeProfileAssignment(EnergyTariffProfileAssignment $assignment): array {
        return [
            'channelId' => $assignment->getChannelId(),
            'profileId' => $assignment->getProfile()?->getId(),
            'profile' => $assignment->getProfile() ? $this->serializeProfile($assignment->getProfile()) : null,
        ];
    }
}
