<?php
namespace SuplaApiBundle\Provider;

use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\Model\IOAuth2Client;

class OAuthStorageWithLegacyPasswordSupport extends OAuthStorage {
    public function checkUserCredentials(IOAuth2Client $client, $username, $password) {
        $credentialsValid = parent::checkUserCredentials($client, $username, $password);
        if (!$credentialsValid) {
            // TODO try to authenticate with old password and migrate it then
        }
        return $credentialsValid;
    }
}
