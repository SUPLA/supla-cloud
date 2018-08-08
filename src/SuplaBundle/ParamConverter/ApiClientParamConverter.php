<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\OAuth\ApiClient;

class ApiClientParamConverter extends AbstractBodyParamConverter {
    public function getConvertedClass(): string {
        return ApiClient::class;
    }

    public function convert(array $requestData) {
        $app = new ApiClient();
        $app->setName($requestData['name'] ?? '');
        Assertion::notBlank($app->getName(), 'Application name cannot be blank.');
        $app->setDescription($requestData['description'] ?? '');
        $app->setRedirectUris($requestData['redirectUris'] ?? []);
        return $app;
    }
}
