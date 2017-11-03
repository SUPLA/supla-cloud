<?php
namespace SuplaApiBundle\Model;

use MyCLabs\Enum\Enum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @method static ApiVersions V2_0()
 * @method static ApiVersions V2_1()
 * @method static ApiVersions V2_2()
 * @method static ApiVersions DEFAULT()
 */
class ApiVersions extends Enum {
    const V2_0 = '2.0';
    const V2_1 = '2.1';
    const V2_2 = '2.2';
    const DEFAULT = self::V2_0;

    public function isRequestedEqualOrGreaterThan(Request $request): bool {
        // -1 lower, 0 equal, 1 greater
        $versionFromRequest = self::fromRequest($request)->getValue();
        $comparison = version_compare($this->getValue(), $versionFromRequest);
        return $comparison <= 0;
    }

    public static function fromRequest(Request $request): ApiVersions {
        $actualVersion = $request->get('version', self::DEFAULT);
        if (!$actualVersion) {
            $actualVersion = self::DEFAULT;
        }
        if (!self::isValid($actualVersion)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Invalid API version requested: $actualVersion. Supported versions: "
                . implode(', ', array_unique(self::toArray()))
            );
        }
        return new self($actualVersion);
    }
}
