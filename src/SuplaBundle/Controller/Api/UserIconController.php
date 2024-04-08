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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\UserIcon;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\UserIconRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Schema(
 *   schema="UserIcon", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="functionId", type="integer", example=60),
 *   @OA\Property(property="function", ref="#/components/schemas/ChannelFunction"),
 *   @OA\Property(property="images", description="Base64-encoded images of this icon in light mode. Returned only if required by the `include` parameter in the single-entity endpoint.", type="array", @OA\Items(type="string")),
 *   @OA\Property(property="imagesDark", description="Base64-encoded images of this icon in dark mode. Returned only if required by the `include` parameter in the single-entity endpoint.", type="array", @OA\Items(type="string")),
 * )
 */
class UserIconController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var UserIconRepository */
    private $userIconRepository;

    public function __construct(UserIconRepository $userIconRepository) {
        $this->userIconRepository = $userIconRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return ['images'];
    }

    /**
     * @OA\Post(
     *   path="/user-icons", operationId="createUserIcon", summary="Create a new User Icon", tags={"User Icons"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Multipart request with files to save as a new icon. The number of images required to be sent with the request is determined by the chosen function identifier (it must match the `function.possibleVisualStates` count). Each image represents the respective visual state from `function.possibleVisualStates` array.",
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="function", ref="#/components/schemas/ChannelFunctionEnumNames"),
     *         @OA\Property(property="sourceIcon", type="integer", description="ID of an existing user icon to replace with these new files. Optional."),
     *         @OA\Property(property="image1", type="string", format="binary"),
     *         @OA\Property(property="image2", type="string", format="binary"),
     *         @OA\Property(property="image3", type="string", format="binary"),
     *         @OA\Property(property="image4", type="string", format="binary"),
     *         @OA\Property(property="imageDark1", type="string", format="binary"),
     *         @OA\Property(property="imageDark2", type="string", format="binary"),
     *         @OA\Property(property="imageDark3", type="string", format="binary"),
     *         @OA\Property(property="imageDark4", type="string", format="binary"),
     *       )
     *     )
     *   ),
     *   @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/UserIcon")),
     * )
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/user-icons")
     * @UnavailableInMaintenance
     */
    public function postIconAction(Request $request) {
        $files = $request->files;
        Assertion::greaterThan(count($files), 0, 'You have not uploaded any files, or the uploaded files are too big.');
        return $this->storeIcon($request, function (string $imageName) use ($files) {
            if ($files->has($imageName)) {
                return new ImageResize($files->get($imageName)->getPathName());
            }
            return null;
        });
    }

    /**
     * @OA\Post(
     *   path="/user-icons.base64", operationId="createUserIconBase64", summary="Create a new User Icon sent in Base64 format.", tags={"User Icons"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Request with Base64-encoded images to save as a new icon. The number of images required to be sent with the request is determined by the chosen function identifier (it must match the `function.possibleVisualStates` count). Each image represents the respective visual state from `function.possibleVisualStates` array.",
     *     @OA\JsonContent(
     *       @OA\Property(property="function", ref="#/components/schemas/ChannelFunctionEnumNames"),
     *       @OA\Property(property="sourceIcon", type="integer", description="ID of an existing user icon to replace with these new files. Optional."),
     *       @OA\Property(property="image1", type="string", format="byte"),
     *       @OA\Property(property="image2", type="string", format="byte"),
     *       @OA\Property(property="image3", type="string", format="byte"),
     *       @OA\Property(property="image4", type="string", format="byte"),
     *       @OA\Property(property="imageDark1", type="string", format="byte"),
     *       @OA\Property(property="imageDark2", type="string", format="byte"),
     *       @OA\Property(property="imageDark3", type="string", format="byte"),
     *       @OA\Property(property="imageDark4", type="string", format="byte"),
     *     ),
     *   ),
     *   @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/UserIcon")),
     * )
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/user-icons.base64")
     * @UnavailableInMaintenance
     */
    public function postIconBase64Action(Request $request) {
        return $this->storeIcon($request, function (string $imageName) use ($request) {
            $image = $request->get($imageName);
            if ($image) {
                $decoded = base64_decode($image);
                if ($decoded) {
                    return ImageResize::createFromString($decoded);
                }
            }
            return null;
        });
    }

    private function adjustImageSize(ImageResize $imageFromRequest) {
        if ($imageFromRequest->getSourceHeight() !== 156 || $imageFromRequest->getSourceWidth() !== 210) {
            $image = $imageFromRequest->resizeToHeight(156, true)->getImageAsString(IMAGETYPE_PNG);
            $image = ImageResize::createFromString($image);
            $image = $image->crop(210, 156);
        } else {
            $image = $imageFromRequest;
        }
        return $image->getImageAsString(IMAGETYPE_PNG);
    }

    private function storeIcon(Request $request, callable $getImageFromRequest) {
        /** @var ChannelFunction $function */
        $function = ChannelFunction::fromString($request->get('function', ''));
        $sourceIcon = $request->get('sourceIcon');
        $icon = new UserIcon($this->getUser(), $function);
        if ($sourceIcon) {
            $sourceIcon = $this->userIconRepository->findForUser($this->getUser(), $sourceIcon);
            Assertion::eq($function->getId(), $sourceIcon->getFunction()->getId(), 'Function of the edited icons mismatch.');
        }
        $imagesCount = count($function->getPossibleVisualStates());
        for ($iconIndex = 1; $iconIndex <= $imagesCount; $iconIndex++) {
            try {
                /** @var ?ImageResize $imageFromRequest */
                $imageFromRequest = $getImageFromRequest('image' . $iconIndex);
                $imageDarkFromRequest = $getImageFromRequest('imageDark' . $iconIndex);
                if (!$sourceIcon) {
                    Assertion::notNull($imageFromRequest, "Icon for this function must consist of $imagesCount images.");
                }
                if ($imageFromRequest) {
                    $imageString = $this->adjustImageSize($imageFromRequest);
                } else {
                    $imageString = $sourceIcon->getImages()[$iconIndex - 1];
                }
                if ($imageDarkFromRequest) {
                    $imageDarkString = $this->adjustImageSize($imageDarkFromRequest);
                } elseif ($sourceIcon) {
                    $imageDarkString = $sourceIcon->getImagesDark()[$iconIndex - 1];
                } else {
                    $imageDarkString = null;
                }
            } catch (ImageResizeException $exception) {
                throw new ApiException($exception->getMessage(), 400, $exception);
            }
            $icon->setImage($imageString, $iconIndex);
            $icon->setImageDark($imageDarkString, $iconIndex);
        }
        $this->transactional(function (EntityManagerInterface $em) use ($icon, $sourceIcon) {
            $em->persist($icon);
            if ($sourceIcon) {
                foreach ($sourceIcon->getChannels() as $channel) {
                    $channel->setUserIcon($icon);
                    $em->persist($channel);
                }
                foreach ($sourceIcon->getChannelGroups() as $channelGroup) {
                    $channelGroup->setUserIcon($icon);
                    $em->persist($channelGroup);
                }
                foreach ($sourceIcon->getScenes() as $scene) {
                    $scene->setUserIcon($icon);
                    $em->persist($scene);
                }
                $em->remove($sourceIcon);
            }
        });
        return $this->view($icon, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *   path="/user-icons", operationId="getUserIcons", summary="List User Icons", tags={"User Icons"},
     *   @OA\Parameter(name="function", in="query", explode=false, required=false, @OA\Schema(type="array", @OA\Items(ref="#/components/schemas/ChannelFunctionEnumNames"))),
     *   @OA\Parameter(name="ids", in="query", explode=false, required=false, @OA\Schema(type="array", @OA\Items(type="integer"))),
     *   @OA\Parameter(
     *     description="List of extra fields to include in the response.",
     *     in="query", name="include", required=false, explode=false,
     *     @OA\Schema(type="array", @OA\Items(type="string", enum={"images"})),
     *   ),
     *   @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UserIcon")))
     * )
     * @Rest\Get("/user-icons", name="user_icons_list")
     * @Security("is_granted('ROLE_CHANNELS_R')")
     */
    public function getUserIconsAction(Request $request) {
        $criteria = Criteria::create();
        if (($function = $request->get('function')) !== null) {
            $functionIds = EntityUtils::mapToIds(ChannelFunction::fromStrings(explode(',', $function)));
            $criteria->andWhere(Criteria::expr()->in('function', $functionIds));
        }
        if (($ids = $request->get('ids')) !== null) {
            $criteria->andWhere(Criteria::expr()->in('id', explode(',', $ids)));
        }
        $channels = $this->getUser()->getUserIcons()->matching($criteria);
        return $this->serializedView($channels, $request);
    }

    /**
     * @OA\Get(
     *   path="/user-icons/{id}", operationId="getUserIcon", summary="Get User Icon", tags={"User Icons"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(
     *     description="List of extra fields to include in the response.",
     *     in="query", name="include", required=false, explode=false,
     *     @OA\Schema(type="array", @OA\Items(type="string", enum={"images"})),
     *   ),
     *   @OA\Response(response="200", description="User Icon image", @OA\JsonContent(ref="#/components/schemas/UserIcon")),
     * )
     * @Rest\Get("/user-icons/{userIcon}")
     * @Security("userIcon.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     */
    public function getUserIconAction(Request $request, UserIcon $userIcon) {
        return $this->serializedView($userIcon, $request);
    }

    /**
     * @OA\Get(
     *   path="/user-icons/{id}/{imageIndex}", operationId="getUserIconImage", summary="Get User Icon image at specified index", tags={"User Icons"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="imageIndex", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="dark", in="query", required=false, @OA\Schema(type="boolean")),
     *   @OA\Response(response="200", description="User Icon image", @OA\MediaType(mediaType="image/*", @OA\Schema(type="string", format="binary"))),
     * )
     * @Rest\Get("/user-icons/{userIcon}/{imageIndex}")
     * @Security("userIcon.belongsToUser(user) and is_granted('ROLE_CHANNELS_FILES')")
     * @Cache(maxage="86400", smaxage=86400)
     */
    public function getUserIconImageAction(Request $request, UserIcon $userIcon, int $imageIndex) {
        if ($request->query->get('dark')) {
            $images = $userIcon->getImagesDark();
        } else {
            $images = $userIcon->getImages();
        }
        if (isset($images[$imageIndex])) {
            return new Response($images[$imageIndex], Response::HTTP_OK, ['Content-Type' => 'image/png']);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @OA\Delete(
     *     path="/user-icons/{id}", operationId="deleteUserIcon", summary="Delete the User Icon", tags={"User Icons"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
     * @Rest\Delete("/user-icons/{userIcon}")
     * @Security("userIcon.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW')")
     * @UnavailableInMaintenance
     */
    public function deleteUserIconAction(UserIcon $userIcon) {
        return $this->transactional(function (EntityManagerInterface $em) use ($userIcon) {
            foreach ($userIcon->getChannels() as $channel) {
                $channel->setUserIcon(null);
                $channel->setAltIcon(0);
                $em->persist($channel);
            }
            $em->remove($userIcon);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }
}
