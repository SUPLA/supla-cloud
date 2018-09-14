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
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AuditEntryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class DirectLinkController extends RestController {
    use Transactional;

    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var EncoderFactory */
    private $encoderFactory;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function __construct(
        ChannelActionExecutor $channelActionExecutor,
        EncoderFactory $encoderFactory,
        AuditEntryRepository $auditEntryRepository
    ) {
        $this->channelActionExecutor = $channelActionExecutor;
        $this->encoderFactory = $encoderFactory;
        $this->auditEntryRepository = $auditEntryRepository;
    }

    /**
     * @Rest\Get("/direct-links")
     * @Security("has_role('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinksAction(Request $request) {
        $directLinks = $this->getUser()->getDirectLInks();
        $view = $this->view($directLinks, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['subject']);
        return $view;
    }

    /**
     * @Rest\Get("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_R')")
     */
    public function getDirectLinkAction(Request $request, DirectLink $directLink) {
        $view = $this->view($directLink, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['subject', 'iodevice']);
        return $view;
    }

    /**
     * @Rest\Post("/direct-links")
     * @Security("has_role('ROLE_DIRECTLINKS_RW')")
     */
    public function postDirectLinkAction(Request $request, DirectLink $directLink) {
        $user = $this->getUser();
        Assertion::false($user->isLimitDirectLinkExceeded(), 'Direct links limit has been exceeded');
        $slug = $this->transactional(function (EntityManagerInterface $em) use ($directLink) {
            $slug = $directLink->generateSlug($this->encoderFactory->getEncoder($directLink));
            $directLink->setEnabled(true);
            $em->persist($directLink);
            return $slug;
        });
        $view = $this->view($directLink, Response::HTTP_CREATED);
        $this->setSerializationGroups($view, $request, ['channel']);
        $view->getContext()->setAttribute('slug', $slug);
        return $view;
    }

    /**
     * @Rest\Put("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_RW')")
     */
    public function putDirectLinkAction(DirectLink $directLink, DirectLink $updated) {
        return $this->transactional(function (EntityManagerInterface $em) use ($directLink, $updated) {
            $directLink->setCaption($updated->getCaption());
            $directLink->setAllowedActions($updated->getAllowedActions());
            $directLink->setActiveFrom($updated->getActiveFrom());
            $directLink->setActiveTo($updated->getActiveTo());
            $directLink->setExecutionsLimit($updated->getExecutionsLimit());
            $directLink->setEnabled($updated->isEnabled());
            $em->persist($directLink);
            return $this->view($directLink, Response::HTTP_OK);
        });
    }

    /**
     * @Rest\Delete("/direct-links/{directLink}")
     * @Security("directLink.belongsToUser(user) and has_role('ROLE_DIRECTLINKS_RW')")
     */
    public function deleteChannelGroupAction(DirectLink $directLink) {
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
    }
}
