<?php
namespace SuplaBundle\Model;

use MyCLabs\Enum\Enum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @method static ApiVersions V2_0()
 * @method static ApiVersions V2_1()
 * @method static ApiVersions V2_2()
 * @method static ApiVersions V2_3()
 * @method static ApiVersions V2_4()
 * @method static ApiVersions DEFAULT()
 * @method static ApiVersions LATEST()
 */
class ApiVersions extends Enum {
    const V2_0 = '2.0.0';
    const V2_1 = '2.1.0';
    const V2_2 = '2.2.0';
    const V2_3 = '2.3.0';
    const V2_4 = '2.4.0';
    const DEFAULT = self::V2_0;
    const LATEST = self::V2_3;

    /** @param Request|array $request */
    public function isRequestedEqualOrGreaterThan($request): bool {
        $versionFromRequest = is_array($request) ? self::fromSerializationContext($request) : self::fromRequest($request)->getValue();
        // -1 lower, 0 equal, 1 greater
        $comparison = version_compare($this->getValue(), $versionFromRequest);
        return $comparison <= 0;
    }

    public static function fromRequest(Request $request): ApiVersions {
        return self::fromString($request->get('version', self::DEFAULT));
    }

    public static function fromSerializationContext(array $context): ApiVersions {
        return isset($context['version']) ? self::fromString($context['version']) : self::DEFAULT();
    }

    public static function fromString(string $version): ApiVersions {
        if ($version) {
            $matched = preg_match('#v?(\d+\.\d+)#i', $version, $matches);
            if ($matched) {
                $requestedVersion = $matches[1] . '.0';
            }
            if (!$matched || !self::isValid($requestedVersion)) {
                throw new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    "Invalid API version requested: $version. Supported versions: "
                    . implode(', ', array_unique(self::toArray()))
                );
            }
            return new self($requestedVersion);
        } else {
            return self::DEFAULT();
        }
    }
}
