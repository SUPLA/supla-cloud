<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Exception\ApiExceptionWithDetails;

class ApiClientParamConverter extends AbstractBodyParamConverter {
    public function getConvertedClass(): string {
        return ApiClient::class;
    }

    public function convert(array $requestData) {
        $app = new ApiClient();
        $app->setName($requestData['name'] ?? '');
        Assertion::notBlank($app->getName(), 'Application name cannot be blank.');
        $app->setDescription($requestData['description'] ?? '');
        $redirectUris = $requestData['redirectUris'] ?? [];
        foreach ($redirectUris as $redirectUri) {
            try {
                Assertion::url($redirectUri);
            } catch (\InvalidArgumentException $e) {
                throw new ApiExceptionWithDetails('Invalid redirect URI: {uri}', ['uri' => $redirectUri]);
            }
        }
        $app->setRedirectUris($redirectUris);
        return $app;
    }
}
