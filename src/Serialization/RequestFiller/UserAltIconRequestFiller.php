<?php

namespace App\Serialization\RequestFiller;

use App\Entity\ActionableSubject;
use App\Repository\UserIconRepository;
use Assert\Assertion;

trait UserAltIconRequestFiller {
    protected function fillUserAltIcon(UserIconRepository $userIconRepository, array $data, ActionableSubject $subject): void {
        if (array_key_exists('userIconId', $data)) {
            $icon = null;
            if ($data['userIconId']) {
                $icon = $userIconRepository->findForUser($subject->getUser(), $data['userIconId']);
                Assertion::eq($icon->getFunction()->getId(), $subject->getFunction()->getId(), 'Chosen user icon is for other function.');
            }
            $subject->setUserIcon($icon);
            $subject->setAltIcon(0);
        }
        if (!$subject->getUserIcon() && array_key_exists('altIcon', $data)) {
            $subject->setAltIcon($data['altIcon'] ?? 0);
        }
    }
}
