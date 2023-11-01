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

namespace SuplaBundle\Controller;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Psr\Cache\CacheItemPoolInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Schema(
 *     schema="ErrorResponse", type="object",
 *     @OA\Property(property="status", type="integer", example="400"),
 *     @OA\Property(property="message", type="string"),
 * )
 */
class DefaultController extends AbstractController {
    /** @var string */
    private $suplaUrl;
    /** @var CacheItemPoolInterface */
    private $openApiCache;

    public function __construct(string $suplaUrl, CacheItemPoolInterface $openApiCache) {
        $this->suplaUrl = $suplaUrl;
        $this->openApiCache = $openApiCache;
    }

    /**
     * @Route("/auth/create", name="_auth_create")
     * @Route("/account/create", name="_account_create")
     * @Route("/account/create_here/{locale}", name="_account_create_here")
     * @UnavailableInMaintenance
     */
    public function createAction(Request $request, $locale = null) {
        if ($this->getUser()) {
            return $this->redirectToRoute('_homepage');
        }
        return $this->redirectToRoute('_register', ['lang' => $request->getLocale()]);
    }

    /**
     * @Route("/api-docs/docs.html", methods={"GET"})
     * @Template()
     */
    public function apiDocsAction(Request $request) {
        return ['supla_url' => $this->suplaUrl, 'yaml_suffix' => ($request->get('v') === '2.3.0' ? '' : '-v3')];
    }

    /**
     * @Route("/api-docs/supla-api-docs.yaml", methods={"GET"})
     */
    public function getApiDocsSchemaAction() {
        $yaml = file_get_contents(\AppKernel::ROOT_PATH . '/config/supla-api-docs.yaml');
        $yaml = str_replace('https://cloud.supla.org', $this->suplaUrl, $yaml);
        return new Response($yaml, Response::HTTP_OK, ['Content-Type' => 'application/yaml']);
    }

    /**
     * @OA\OpenApi(
     *   security={{"BearerAuth": {}}, {"OAuth2": {}}},
     *   @OA\Info(title="SUPLA Cloud API", version="X.X.X"),
     *   @OA\Server(url="https://cloud.supla.org/api/v3"),
     * )
     * @OA\SecurityScheme(securityScheme="BearerAuth", type="http", scheme="bearer")
     * @OA\SecurityScheme(securityScheme="OAuth2", type="oauth2", @OA\Flow(
     *   flow="authorizationCode", authorizationUrl="https://cloud.supla.org/oauth/v2/auth", tokenUrl="https://cloud.supla.org/oauth/v2/token",
     *   scopes={
     *     "accessids_r": "Access Identifiers (read)",
     *     "accessids_rw": "Access Identifiers (read and modify)",
     *     "account_r": "User account and e-mail address (read)",
     *     "account_rw": "User account (read and modify)",
     *     "channels_r": "Channels (read)",
     *     "channels_rw": "Channels (read and modify)",
     *     "channels_ea": "Channels (execute actions)",
     *     "channels_files": "Download files from API (measurements history, user icons)",
     *     "channelgroups_r": "Channel groups (read)",
     *     "channelgroups_rw": "Channel groups (read and modify)",
     *     "channelgroups_ea": "Channel groups (execute actions)",
     *     "clientapps_r": "Client apps (read)",
     *     "clientapps_rw": "Client apps (read and modify)",
     *     "directlinks_r": "Direct links (read)",
     *     "directlinks_rw": "Direct links (read and modify)",
     *     "iodevices_r": "IO Devices (read)",
     *     "iodevices_rw": "IO Devices (read and modify)",
     *     "locations_r": "Locations (read)",
     *     "locations_rw": "Locations (read and modify)",
     *     "scenes_r": "Scenes (read)",
     *     "scenes_rw": "Scenes (read and modify)",
     *     "scenes_ea": "Scenes (execute actions)",
     *     "schedules_r": "Schedules (read)",
     *     "schedules_rw": "Schedules (read and modify)",
     *     "state_webhook": "Access to state webhooks",
     *     "mqtt_broker": "MQTT Broker settings",
     *     "offline_access": "Issue refresh token",
     *   }
     * ))
     * @Route("/api-docs/supla-api-docs-v3.yaml", methods={"GET"})
     */
    public function getApiDocsSchemaActionV24() {
        $version = $this->getParameter('supla.version');
        $cacheItem = $this->openApiCache->getItem('openApi' . $version);
        if ($cacheItem->isHit() && defined('APPLICATION_ENV') && APPLICATION_ENV === 'prod') {
            $yaml = $cacheItem->get();
        } else {
            $openapi = Generator::scan([
                __DIR__,
                __DIR__ . '/../Enums',
                __DIR__ . '/../Model',
            ]);
            $openapi->info = new OA\Info(['title' => 'SUPLA Cloud API', 'version' => $version]);
            $yaml = $openapi->toYaml();
            $yaml = str_replace('https://cloud.supla.org', $this->suplaUrl, $yaml);
            $cacheItem->set($yaml);
            $this->openApiCache->save($cacheItem);
        }
        return new Response($yaml, Response::HTTP_OK, ['Content-Type' => 'application/yaml']);
    }

    /**
     * @Route("/api-docs/oauth2-redirect.html", methods={"GET"})
     * @Template()
     */
    public function apiDocsOAuth2RedirectAction() {
    }

    /**
     * @Route("/", name="_homepage")
     * @Route("/register", name="_register")
     * @Route("/auth/login", name="_obsolete_login")
     * @Route("/{suffix}", requirements={"suffix"="^(?!api|oauth/|direct/).*"}, methods={"GET"})
     * @Template()
     * @Cache(expires="2016-01-01")
     */
    public function spaBoilerplateAction($suffix = null) {
        if ($suffix && preg_match('#\..{2,4}$#', $suffix)) {
            throw new NotFoundHttpException("$suffix file could not be found");
        }
    }
}
