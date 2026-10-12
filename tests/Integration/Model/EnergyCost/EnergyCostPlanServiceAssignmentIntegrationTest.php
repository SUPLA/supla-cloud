<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Model\EnergyCost;

use App\Auth\SuplaOAuth2Authenticator;
use App\Entity\Main\AccessID;
use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Exception\ApiException;
use App\Model\EnergyCost\EnergyCostPlanService;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\UserFixtures;
use DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EnergyCostPlanServiceAssignmentIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    public function testAssignsReplacesAndDeletesAssignment(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $firstPlan = $this->persistPlan($user, 'First');
        $secondPlan = $this->persistPlan($user, 'Second');

        $assignment = $this->service()->assignToChannel($user, $channel, $firstPlan->getId());
        $this->assertSame($firstPlan->getId(), $assignment->getEnergyCostPlan()->getId());

        $assignment = $this->service()->assignToChannel($user, $channel, $secondPlan->getId());
        $this->assertSame($secondPlan->getId(), $assignment->getEnergyCostPlan()->getId());
        $this->assertSame($secondPlan->getId(), $this->repository()->findPlanForChannel($channel)->getId());

        $this->service()->deleteAssignment($user, $channel);
        $this->assertNull($this->repository()->findForChannel($channel));
    }

    public function testHidesForeignPlan(): void {
        $user = $this->createConfirmedUser('owner@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        $foreignPlan = $this->persistPlan($this->createConfirmedUser('other@supla.org'), 'Other');

        $this->expectException(NotFoundHttpException::class);
        $this->service()->assignToChannel($user, $channel, $foreignPlan->getId());
    }

    public function testRejectsAnotherUsersChannel(): void {
        $owner = $this->createConfirmedUser('channel-owner@supla.org');
        $user = $this->createConfirmedUser('other-user@supla.org');
        $plan = $this->persistPlan($user, 'Home');

        $this->expectException(AccessDeniedHttpException::class);
        $this->service()->assignToChannel($user, $this->createElectricityMeterChannel($owner), $plan->getId());
    }

    public function testRejectsUnsupportedChannelFunction(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]],
        )->getChannels()->first();

        $this->expectException(ApiException::class);
        $this->service()->assignToChannel($user, $channel, $this->persistPlan($user, 'Home')->getId());
    }

    /** @dataProvider sharedOperations */
    public function testAccessIdRestrictionsPreventIndirectChangesToOtherMeters(string $operation): void {
        $user = $this->createConfirmedUser();
        $first = $this->createElectricityMeterChannel($user);
        $second = $this->createElectricityMeterChannel($user);
        $plan = $this->persistPlan($user, 'Shared');
        $this->service()->assignToChannel($user, $first, $plan->getId());
        $this->service()->assignToChannel($user, $second, $plan->getId());
        $accessId = new AccessID();
        $accessId->getLocations()->add($first->getLocation());
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_CHANNELS_RW', 'ROLE_CHANNELS_R']);
        $token->setAttribute(SuplaOAuth2Authenticator::REQUEST_ATTRIBUTE_ACCESS_ID, $accessId);
        self::$container->get('security.token_storage')->setToken($token);

        $this->expectException(AccessDeniedHttpException::class);
        if ($operation === 'save') {
            $this->service()->saveForChannel($user, $first, $this->configuration(), true);
        } elseif ($operation === 'update') {
            $this->service()->update($user, $plan->getId(), 'Changed', $this->configuration());
        } else {
            $this->service()->delete($user, $plan->getId());
        }
    }

    public static function sharedOperations(): array {
        return [['save'], ['update'], ['delete']];
    }

    public function testRestrictedClientCanSavePrivateCopyWithoutChangingInaccessibleMeter(): void {
        $user = $this->createConfirmedUser();
        $first = $this->createElectricityMeterChannel($user);
        $second = $this->createElectricityMeterChannel($user);
        $plan = $this->persistPlan($user, 'Shared');
        $this->service()->assignToChannel($user, $first, $plan->getId());
        $this->service()->assignToChannel($user, $second, $plan->getId());
        $accessId = new AccessID();
        $accessId->getLocations()->add($first->getLocation());
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_CHANNELS_RW', 'ROLE_CHANNELS_R']);
        $token->setAttribute(SuplaOAuth2Authenticator::REQUEST_ATTRIBUTE_ACCESS_ID, $accessId);
        self::$container->get('security.token_storage')->setToken($token);
        $configuration = $this->configuration();
        $configuration['periods'][0]['components'][0]['values']['energy.rate'] = '0.80';
        $result = $this->service()->saveForChannel($user, $first, $configuration, false);
        $this->assertNotSame($plan->getId(), $result['plan']->getId());
        $this->assertSame($plan->getId(), $this->repository()->findForChannel($second)->getEnergyCostPlan()->getId());
        $this->assertSame('0.71', $plan->getConfiguration()['periods'][0]['components'][0]['values']['energy.rate']);
    }

    private function service(): EnergyCostPlanService {
        return self::$container->get(EnergyCostPlanService::class);
    }

    private function repository(): EnergyCostPlanAssignmentRepository {
        /** @var EnergyCostPlanAssignmentRepository $repository */
        $repository = $this->getEntityManager()->getRepository(EnergyCostPlanAssignment::class);
        return $repository;
    }

    private function createElectricityMeterChannel(User $user): IODeviceChannel {
        return $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
    }

    private function persistPlan(User $user, string $name): EnergyCostPlan {
        $plan = new EnergyCostPlan($user, $name, $this->configuration(), new DateTime('2026-01-01T00:00:00+00:00'));
        $this->getEntityManager()->persist($plan);
        $this->getEntityManager()->flush();
        return $plan;
    }

    /** @return array<string, mixed> */
    private function configuration(): array {
        return [
            'version' => 2,
            'currency' => 'PLN',
            'timezone' => 'Europe/Warsaw',
            'billingCycles' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'anchor' => '2026-01-15',
                'length' => 1,
                'unit' => 'MONTH',
            ]],
            'periods' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'components' => [
                    ['kind' => 'ENERGY_PURCHASE', 'presetId' => 'PL.TAURON_SPRZEDAZ.G11', 'componentId' => 'energy-purchase',
                        'values' => ['energy.rate' => '0.71']],
                    ['kind' => 'DISTRIBUTION_VARIABLE', 'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11',
                        'componentId' => 'distribution-variable', 'values' => []],
                ],
            ]],
        ];
    }
}
