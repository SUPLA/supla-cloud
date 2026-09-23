<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Controller\Api;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use App\EventListener\UnavailableInMaintenance;
use App\Exception\ApiException;
use App\Model\EnergyCost\EnergyCostPlanCalculator;
use App\Model\EnergyCost\EnergyCostPlanService;
use Assert\Assertion;
use DateTimeImmutable;
use DateTimeZone;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Supla\EnergyCostCalculator\Exception\CalculationException;
use Supla\EnergyCostCalculator\Exception\CostPlanDefinitionException;
use Supla\EnergyCostCalculator\Exception\DefinitionException;
use Supla\EnergyCostCalculator\Exception\EnergyCostCalculatorException;
use Supla\EnergyCostCalculator\Exception\InvalidTariffPresetException;
use Supla\EnergyCostCalculator\Exception\MissingReferenceDataException;
use Supla\EnergyCostCalculator\Exception\TariffPresetCompilationException;
use Supla\EnergyCostCalculator\Exception\TariffPresetNotFoundException;
use Supla\EnergyCostCalculator\Preset\TariffPresetCatalog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Schema(
 *   schema="EnergyCostPlan", type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="configuration", type="object"),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 * )
 * @OA\Schema(
 *   schema="EnergyCostPlanAssignment", type="object",
 *   @OA\Property(property="channelId", type="integer"),
 *   @OA\Property(property="planId", type="integer", format="int64"),
 * )
 * @OA\Schema(
 *   schema="EnergyTariffPreset", type="object",
 *   @OA\Property(property="id", type="string"),
 *   @OA\Property(property="revision", type="string"),
 *   @OA\Property(property="metadata", type="object"),
 *   @OA\Property(property="document", type="object"),
 * )
 */
class EnergyCostPlanController extends RestController {
    public function __construct(
        private readonly EnergyCostPlanService $planService,
        private readonly EnergyCostPlanCalculator $calculator,
        private readonly TariffPresetCatalog $catalog,
    ) {
    }

    /**
     * @OA\Get(path="/energy-tariff-presets", operationId="getEnergyTariffPresets", summary="Get energy tariff presets", tags={"Energy cost"}, @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(type="object"))))
     * @Rest\Get("/energy-tariff-presets")
     * @Security("is_granted('ROLE_CHANNELS_R')")
     */
    public function getEnergyTariffPresetsAction(): View {
        try {
            return $this->view($this->catalog->presets());
        } catch (EnergyCostCalculatorException $exception) {
            $this->throwCalculatorException($exception);
        }
    }

    /**
     * @OA\Get(path="/energy-tariff-presets/{presetId}", operationId="getEnergyTariffPreset", summary="Get energy tariff preset", tags={"Energy cost"}, @OA\Parameter(name="presetId", in="path", required=true, @OA\Schema(type="string")), @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/EnergyTariffPreset")), @OA\Response(response="404", description="Preset does not exist", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @Rest\Get("/energy-tariff-presets/{presetId}")
     * @Security("is_granted('ROLE_CHANNELS_R')")
     */
    public function getEnergyTariffPresetAction(string $presetId): View {
        try {
            $preset = $this->catalog->get($presetId);
            return $this->view(['id' => $preset->id, 'revision' => $preset->revision, 'metadata' => $preset->metadata, 'document' => $preset->document]);
        } catch (EnergyCostCalculatorException $exception) {
            $this->throwCalculatorException($exception);
        }
    }

    /**
     * @OA\Get(
     *   path="/energy-cost-plans", operationId="getEnergyCostPlans", summary="Get energy cost plans", tags={"Energy cost"},
     *   @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/EnergyCostPlan"))),
     * )
     * @Rest\Get("/energy-cost-plans")
     * @Security("is_granted('ROLE_CHANNELS_R')")
     */
    public function getEnergyCostPlansAction(): View {
        return $this->view(array_map($this->serializePlan(...), $this->planService->getAll($this->getUser())));
    }

    /**
     * @OA\Post(
     *   path="/energy-cost-plans", operationId="createEnergyCostPlan", summary="Create energy cost plan", tags={"Energy cost"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(required={"name", "configuration"}, @OA\Property(property="name", type="string"), @OA\Property(property="configuration", type="object"))),
     *   @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/EnergyCostPlan")),
     * )
     * @Rest\Post("/energy-cost-plans")
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @UnavailableInMaintenance
     */
    public function postEnergyCostPlanAction(Request $request): View {
        [$name, $configuration] = $this->requestData($request);
        try {
            $plan = $this->planService->create($this->getUser(), $name, $configuration);
            return $this->view($this->serializePlan($plan), Response::HTTP_CREATED);
        } catch (EnergyCostCalculatorException $exception) {
            $this->throwCalculatorException($exception);
        }
    }

    /**
     * @OA\Get(
     *   path="/energy-cost-plans/{planId}", operationId="getEnergyCostPlan", summary="Get energy cost plan", tags={"Energy cost"},
     *   @OA\Parameter(name="planId", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *   @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/EnergyCostPlan")),
     *   @OA\Response(response="404", description="Plan does not exist", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @Rest\Get("/energy-cost-plans/{planId}", requirements={"planId"="^\d+$"})
     * @Security("is_granted('ROLE_CHANNELS_R')")
     */
    public function getEnergyCostPlanAction(string $planId): View {
        return $this->view($this->serializePlan($this->planService->get($this->getUser(), $planId)));
    }

    /**
     * @OA\Put(
     *   path="/energy-cost-plans/{planId}", operationId="updateEnergyCostPlan", summary="Replace energy cost plan", tags={"Energy cost"},
     *   @OA\Parameter(name="planId", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(required={"name", "configuration"}, @OA\Property(property="name", type="string"), @OA\Property(property="configuration", type="object"))),
     *   @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/EnergyCostPlan")),
     * )
     * @Rest\Put("/energy-cost-plans/{planId}", requirements={"planId"="^\d+$"})
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @UnavailableInMaintenance
     */
    public function putEnergyCostPlanAction(Request $request, string $planId): View {
        // Preserve owner-scoped 404 behavior before inspecting a submitted document.
        $this->planService->get($this->getUser(), $planId);
        [$name, $configuration] = $this->requestData($request);
        try {
            return $this->view($this->serializePlan($this->planService->update($this->getUser(), $planId, $name, $configuration)));
        } catch (EnergyCostCalculatorException $exception) {
            $this->throwCalculatorException($exception);
        }
    }

    /**
     * @OA\Delete(
     *   path="/energy-cost-plans/{planId}", operationId="deleteEnergyCostPlan", summary="Delete energy cost plan", tags={"Energy cost"},
     *   @OA\Parameter(name="planId", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *   @OA\Response(response="204", description="Success"),
     * )
     * @Rest\Delete("/energy-cost-plans/{planId}", requirements={"planId"="^\d+$"})
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @UnavailableInMaintenance
     */
    public function deleteEnergyCostPlanAction(string $planId): Response {
        $this->planService->delete($this->getUser(), $planId);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(path="/channels/{channel}/energy-cost-plan-assignment", operationId="getChannelEnergyCostPlanAssignment", summary="Get channel energy cost plan assignment", tags={"Energy cost"}, @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/EnergyCostPlanAssignment")), @OA\Response(response="404", description="Assignment does not exist", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")))
     * @Rest\Get("/channels/{channel}/energy-cost-plan-assignment")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getEnergyCostPlanAssignmentAction(IODeviceChannel $channel): View {
        return $this->view($this->serializeAssignment($this->planService->getAssignment($this->getUser(), $channel)));
    }

    /**
     * @OA\Put(path="/channels/{channel}/energy-cost-plan-assignment", operationId="updateChannelEnergyCostPlanAssignment", summary="Assign energy cost plan to channel", tags={"Energy cost"}, @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(required={"planId"}, @OA\Property(property="planId", type="integer", format="int64"))), @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/EnergyCostPlanAssignment")))
     * @Rest\Put("/channels/{channel}/energy-cost-plan-assignment")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @UnavailableInMaintenance
     */
    public function putEnergyCostPlanAssignmentAction(Request $request, IODeviceChannel $channel): View {
        $data = $request->request->all();
        Assertion::keyExists($data, 'planId', 'Missing planId.');
        Assertion::integer($data['planId'], 'Invalid planId.');
        Assertion::greaterThan($data['planId'], 0, 'Invalid planId.');
        return $this->view($this->serializeAssignment($this->planService->assignToChannel($this->getUser(), $channel, $data['planId'])));
    }

    /**
     * @OA\Delete(path="/channels/{channel}/energy-cost-plan-assignment", operationId="deleteChannelEnergyCostPlanAssignment", summary="Delete channel energy cost plan assignment", tags={"Energy cost"}, @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response="204", description="Success"))
     * @Rest\Delete("/channels/{channel}/energy-cost-plan-assignment")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @UnavailableInMaintenance
     */
    public function deleteEnergyCostPlanAssignmentAction(IODeviceChannel $channel): Response {
        $this->planService->deleteAssignment($this->getUser(), $channel);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(path="/channels/{channel}/energy-cost-calculation", operationId="calculateChannelEnergyCost", summary="Calculate channel energy cost", tags={"Energy cost"}, @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="fromTimestamp", in="query", required=true, @OA\Schema(type="integer", format="int64")), @OA\Parameter(name="toTimestamp", in="query", required=true, @OA\Schema(type="integer", format="int64")), @OA\Response(response="200", description="Success", @OA\JsonContent(type="object")))
     * @Rest\Get("/channels/{channel}/energy-cost-calculation")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getEnergyCostCalculationAction(Request $request, IODeviceChannel $channel): JsonResponse {
        $fromTimestamp = $this->timestamp($request, 'fromTimestamp');
        $toTimestamp = $this->timestamp($request, 'toTimestamp');
        Assertion::lessThan($fromTimestamp, $toTimestamp, 'fromTimestamp must be lower than toTimestamp.');
        $utc = new DateTimeZone('UTC');
        try {
            $result = $this->calculator->calculate(
                $this->getUser(),
                $channel,
                (new DateTimeImmutable('@' . $fromTimestamp))->setTimezone($utc),
                (new DateTimeImmutable('@' . $toTimestamp))->setTimezone($utc),
            );
            return new JsonResponse($result);
        } catch (EnergyCostCalculatorException $exception) {
            $this->throwCalculatorException($exception);
        }
    }

    /** @return array{string, array<string, mixed>} */
    private function requestData(Request $request): array {
        $data = $request->request->all();
        Assertion::keyExists($data, 'name', 'Missing name.');
        Assertion::string($data['name'], 'Invalid name.');
        $name = trim($data['name']);
        Assertion::notBlank($name, 'Name cannot be empty.');
        Assertion::maxLength($name, 255, 'Name is too long.');
        Assertion::keyExists($data, 'configuration', 'Missing configuration.');
        Assertion::isArray($data['configuration'], 'Invalid configuration.');
        return [$name, $data['configuration']];
    }

    private function timestamp(Request $request, string $name): int {
        $value = $request->query->get($name);
        Assertion::string($value, "Missing or invalid $name.");
        Assertion::regex($value, '/^-?\d+$/', "Missing or invalid $name.");
        $timestamp = filter_var($value, FILTER_VALIDATE_INT);
        Assertion::true($timestamp !== false, "Missing or invalid $name.");
        return $timestamp;
    }

    /** @return array<string, mixed> */
    private function serializePlan(EnergyCostPlan $plan): array {
        return [
            'id' => (int)$plan->getId(),
            'name' => $plan->getName(),
            'configuration' => $plan->getConfiguration(),
            'createdAt' => $plan->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $plan->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    /** @return array<string, int> */
    private function serializeAssignment(EnergyCostPlanAssignment $assignment): array {
        return [
            'channelId' => $assignment->getChannelId(),
            'planId' => (int)$assignment->getEnergyCostPlan()->getId(),
        ];
    }

    private function throwCalculatorException(EnergyCostCalculatorException $exception): never {
        if ($exception instanceof TariffPresetNotFoundException) {
            throw new NotFoundHttpException('Energy tariff preset does not exist.', $exception);
        }
        if ($exception instanceof CostPlanDefinitionException) {
            throw new ApiException('Invalid energy cost plan configuration.', Response::HTTP_BAD_REQUEST, $exception);
        }
        if ($exception instanceof TariffPresetCompilationException || $exception instanceof DefinitionException) {
            throw new ApiException('Energy cost plan configuration could not be compiled.', Response::HTTP_UNPROCESSABLE_ENTITY, $exception);
        }
        if ($exception instanceof InvalidTariffPresetException) {
            throw new ApiException('Energy tariff preset catalogue is unavailable.', Response::HTTP_INTERNAL_SERVER_ERROR, $exception);
        }
        if ($exception instanceof MissingReferenceDataException) {
            throw new ApiException(
                'Reference data is incomplete for the requested calculation range.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception,
            );
        }
        if ($exception instanceof CalculationException) {
            throw new ApiException(
                'Energy cost calculation could not be completed for the requested range.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception,
            );
        }
        throw new ApiException('Energy cost calculator could not complete the request.', Response::HTTP_UNPROCESSABLE_ENTITY, $exception);
    }
}
