<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Auth;

use App\Entity\Main\OAuth\AuthCode;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** Validation and verification for the optional, S256-only PKCE policy. */
class OAuthPkce {
    /** @return array{?string, ?string} */
    public function getChallenge(Request $request, bool $emptyFormPairIsAbsent = false): array {
        $query = $request->query->all();
        $hasChallenge = array_key_exists('code_challenge', $query);
        $hasMethod = array_key_exists('code_challenge_method', $query);
        if (
            $emptyFormPairIsAbsent && $hasChallenge && $hasMethod
            && $query['code_challenge'] === '' && $query['code_challenge_method'] === ''
        ) {
            return [null, null];
        }
        if (!$hasChallenge && !$hasMethod) {
            return [null, null];
        }

        $challenge = $query['code_challenge'] ?? null;
        $method = $query['code_challenge_method'] ?? null;
        if (
            !$hasChallenge || !is_string($challenge) || !$this->isValidChallenge($challenge)
            || !is_string($method) || $method !== 'S256'
        ) {
            throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST);
        }

        return [$challenge, $method];
    }

    public function verify(AuthCode $authCode, Request $request): void {
        $input = $request->getMethod() === 'POST' ? $request->request->all() : $request->query->all();
        $hasVerifier = array_key_exists('code_verifier', $input);
        $verifier = $input['code_verifier'] ?? null;
        if ($authCode->getCodeChallenge() === null) {
            if ($hasVerifier) {
                throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST);
            }
            return;
        }

        if (
            !$hasVerifier || !is_string($verifier) || !$this->isValidVerifier($verifier)
            || $authCode->getCodeChallengeMethod() !== 'S256'
        ) {
            throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT);
        }

        $actualChallenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        if (!hash_equals($authCode->getCodeChallenge(), $actualChallenge)) {
            throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT);
        }
    }

    private function isValidVerifier(string $value): bool {
        return preg_match('/^[A-Za-z0-9\-._~]{43,128}$/D', $value) === 1;
    }

    private function isValidChallenge(string $value): bool {
        if (preg_match('/^[A-Za-z0-9_-]{43}$/D', $value) !== 1) {
            return false;
        }
        $decoded = base64_decode(strtr($value, '-_', '+/') . '=', true);
        return $decoded !== false && strlen($decoded) === 32
            && hash_equals($value, rtrim(strtr(base64_encode($decoded), '+/', '-_'), '='));
    }
}
