<?php
/*
 Copyright (C) AC SOFTWARE SP. Z.O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Form\Type\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/** Preserve optional PKCE fields across FOS's existing browser consent POST. */
class AuthorizePkceFormTypeExtension extends AbstractTypeExtension {
    public function __construct(private readonly RequestStack $requestStack) {
    }

    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $request = $this->requestStack->getCurrentRequest();
        foreach (['code_challenge', 'code_challenge_method'] as $name) {
            $builder->add($name, HiddenType::class, [
                'mapped' => false,
                'data' => $request?->query->get($name),
            ]);
        }
    }

    public static function getExtendedTypes(): iterable {
        return [\FOS\OAuthServerBundle\Form\Type\AuthorizeFormType::class];
    }
}
