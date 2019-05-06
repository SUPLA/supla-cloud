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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AuditEntryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @Rest\Get("/direct-links")
     * @Security("has_role('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinksAction(Request $request) {
        $directLinks = $this->getUser()->getDirectLinks();
        if (($subjectType = $request->get('subjectType')) && ($subjectId = $request->get('subjectId'))) {
            $type = ActionableSubjectType::fromString($subjectType);
            $directLinks = $directLinks->filter(function (DirectLink $directLink) use ($subjectId, $type) {
                return $directLink->getSubjectType() == $type && $directLink->getSubject()->getId() == $subjectId;
            });
        }
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @Rest\Get("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinkAction(Request $request, DirectLink $directLink) {
        return $this->serializedView($directLink, $request, ['subject.relationsCount']);
    }

    /**
     * @Rest\Post("/direct-links")
     * @Security("has_role('ROLE_DIRECTLINKS_RW')")
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
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_RW')")
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
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_RW')")
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
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_R')")
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
