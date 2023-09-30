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

namespace SuplaBundle\Controller\Api;

use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\HasSubject;
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AuditEntryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @OA\Schema(
 *   schema="DirectLink", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="caption", type="string", description="Caption set by the user"),
 *   @OA\Property(property="active", type="boolean"),
 *   @OA\Property(property="disableHttpGet", type="boolean"),
 *   @OA\Property(property="enabled", type="boolean"),
 *   @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
 *   @OA\Property(property="subjectId", type="integer"),
 *   @OA\Property(property="lastUsed", type="string", format="date-time"),
 *   @OA\Property(property="lastIpv4", type="string", format="ipv4"),
 *   @OA\Property(property="userId", type="integer"),
 *   @OA\Property(property="executionsLimit", type="integer"),
 *   @OA\Property(property="allowedActions", type="array", @OA\Items(ref="#/components/schemas/ChannelFunctionActionEnumNames")),
 *   @OA\Property(property="activeDateRange", type="object",
 *     @OA\Property(property="dateStart", type="string", format="date-time"),
 *     @OA\Property(property="dateEnd", type="string", format="date-time"),
 *   ),
 *   @OA\Property(property="subject", description="Only if requested by the `include` param.", ref="#/components/schemas/ActionableSubject"),
 * )
 */
class DirectLinkController extends RestController {
    use Transactional;

    /** @var EncoderFactory */
    private $encoderFactory;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function __construct(EncoderFactoryInterface $encoderFactory, AuditEntryRepository $auditEntryRepository) {
        $this->encoderFactory = $encoderFactory;
        $this->auditEntryRepository = $auditEntryRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return [
            'subject',
            'subject' => 'directLink.subject',
        ];
    }

    private function returnDirectLinks(): Collection {
        return $this->getUser()->getDirectLinks();
    }

    private function returnDirectLinksFilteredBySubject(ActionableSubject $subject): Collection {
        $type = ActionableSubjectType::forEntity($subject);
        return $this->returnDirectLinks()->filter(function (HasSubject $entity) use ($subject, $type) {
            return $entity->getSubjectType() == $type && $entity->getSubject()->getId() == $subject->getId();
        });
    }

    /**
     * @OA\Get(
     *     path="/direct-links", operationId="getDirectLinks", summary="Get Direct Links", tags={"Direct Links"},
     *     @OA\Parameter(name="subjectType", in="query", description="Return links only for particular subjectType. Must be used with subjectId.", required=false, @OA\Schema(ref="#/components/schemas/ActionableSubjectTypeNames")),
     *     @OA\Parameter(name="subjectId", in="query", description="Return links only for particular subjectId. Must be used with subjectType.", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/DirectLink"))),
     * )
     * @Rest\Get("/direct-links")
     * @Security("is_granted('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinksAction(Request $request) {
        $directLinks = $this->returnDirectLinks();
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            if (($subjectType = $request->get('subjectType')) && ($subjectId = $request->get('subjectId'))) {
                $type = ActionableSubjectType::fromString($subjectType);
                $directLinks = $directLinks->filter(function (DirectLink $directLink) use ($subjectId, $type) {
                    return $directLink->getSubjectType() == $type && $directLink->getSubject()->getId() == $subjectId;
                });
            }
        }
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/direct-links", operationId="getChannelDirectLinks", summary="Get channel direct links", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/DirectLink"))),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/direct-links")
     */
    public function getChannelDirectLinksAction(IODeviceChannel $channel, Request $request) {
        $directLinks = $this->returnDirectLinksFilteredBySubject($channel);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/channel-groups/{channelGroup}/direct-links", operationId="getChannelGroupDirectLinks", summary="Get channel group direct links", tags={"Channel Groups"},
     *     @OA\Parameter(description="ID", in="path", name="channelGroup", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/DirectLink"))),
     * )
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_R')")
     * @Rest\Get("/channel-groups/{channelGroup}/direct-links")
     */
    public function getChannelGroupDirectLinksAction(IODeviceChannelGroup $channelGroup, Request $request) {
        $directLinks = $this->returnDirectLinksFilteredBySubject($channelGroup);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/scenes/{scene}/direct-links", operationId="getSceneDirectLinks", summary="Get scene direct links", tags={"Scenes"},
     *     @OA\Parameter(description="ID", in="path", name="scene", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/DirectLink"))),
     * )
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_R')")
     * @Rest\Get("/scenes/{scene}/direct-links")
     */
    public function getSceneDirectLinksAction(Scene $scene, Request $request) {
        $directLinks = $this->returnDirectLinksFilteredBySubject($scene);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/direct-links/{directLink}", operationId="getDirectLink", summary="Get direct link", tags={"Direct Links"},
     *     @OA\Parameter(description="ID", in="path", name="directLink", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/DirectLink")),
     * )
     * @Rest\Get("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and is_granted('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinkAction(Request $request, DirectLink $directLink) {
        return $this->serializedView($directLink, $request, ['subject.relationsCount']);
    }

    /**
     * @Rest\Post("/direct-links")
     * @Security("is_granted('ROLE_DIRECTLINKS_RW')")
     * @UnavailableInMaintenance
     */
    public function postDirectLinkAction(Request $request, DirectLink $directLink, TranslatorInterface $translator) {
        $user = $this->getUser();
        if (!$directLink->getCaption()) {
            $caption = $translator->trans('Direct link', [], null, $user->getLocale());
            $directLink->setCaption($caption . ' #' . ($user->getDirectLinks()->count() + 1));
        }
        Assertion::false($user->isLimitDirectLinkExceeded(), 'Direct links limit has been exceeded'); // i18n
        $slug = $this->transactional(function (EntityManagerInterface $em) use ($directLink) {
            $slug = $directLink->generateSlug($this->encoderFactory->getEncoder($directLink));
            $directLink->setEnabled(true);
            $em->persist($directLink);
            return $slug;
        });
        $view = $this->serializedView($directLink, $request, ['subject.relationsCount'], Response::HTTP_CREATED);
        $view->getContext()->setAttribute('slug', $slug);
        return $view;
    }

    /**
     * @Rest\Put("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and is_granted('ROLE_DIRECTLINKS_RW')")
     * @UnavailableInMaintenance
     */
    public function putDirectLinkAction(DirectLink $directLink, DirectLink $updated, Request $request) {
        $directLink = $this->transactional(function (EntityManagerInterface $em) use ($directLink, $updated) {
            $directLink->setCaption($updated->getCaption());
            $directLink->setAllowedActions($updated->getAllowedActions());
            $directLink->setActiveFrom($updated->getActiveFrom());
            $directLink->setActiveTo($updated->getActiveTo());
            $directLink->setExecutionsLimit($updated->getExecutionsLimit());
            $directLink->setEnabled($updated->isEnabled());
            $directLink->setDisableHttpGet($updated->getDisableHttpGet());
            $em->persist($directLink);
            return $directLink;
        });
        return $this->getDirectLinkAction($request, $directLink);
    }

    /**
     * @Rest\Delete("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and is_granted('ROLE_DIRECTLINKS_RW')")
     * @UnavailableInMaintenance
     */
    public function deleteDirectLinkAction(DirectLink $directLink) {
        return $this->transactional(function (EntityManagerInterface $em) use ($directLink) {
            $em->remove($directLink);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/direct-links/{directLink}/audit")
     * @Security("directLink.belongsToUser(user) and is_granted('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinkAuditAction(DirectLink $directLink, Request $request) {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 10);
        Assertion::greaterOrEqualThan($page, 1, 'Page should be at least 1.');
        Assertion::between($pageSize, 5, 100, 'Page size should be between 5 and 100.');
        return $this->transactional(function (EntityManagerInterface $em) use ($directLink, $pageSize, $page) {
            $this->fixMysqlDistinctMode($em);
            $query = $this->auditEntryRepository->createQueryBuilder('ae')
                ->where('ae.event IN(:events)')
                ->andWhere('ae.intParam = :directLinkId')
                ->orderBy('ae.createdAt', 'DESC')
                ->setFirstResult(($page - 1) * $pageSize)
                ->setMaxResults($pageSize)
                ->setParameters([
                    'events' => [AuditedEvent::DIRECT_LINK_EXECUTION, AuditedEvent::DIRECT_LINK_EXECUTION_FAILURE],
                    'directLinkId' => $directLink->getId(),
                ]);
            $entries = new Paginator($query);
            $view = $this->view($entries, Response::HTTP_OK);
            $view->setHeader('X-Total-Count', count($entries));
            return $view;
        });
    }

    /**
     * Fixes "invalid syntax" error for correct SQL query with DISTINCT mode in MySQL 5.7+.
     * @see https://stackoverflow.com/a/37508414/878514
     * @see https://github.com/doctrine/doctrine2/issues/5622#issuecomment-231727355
     */
    private function fixMysqlDistinctMode(EntityManagerInterface $em) {
        $em->getConnection()->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");
    }
}
