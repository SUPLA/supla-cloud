<?php
/*
 src/SuplaBundle/EventListener/LocaleListener.php

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
namespace SuplaBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface {

    private $default_locale;
    private $container;

    public function __construct($default_locale) {
        $this->default_locale = $default_locale;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            if ($locale === null) {
                $locale =  strtolower(preg_replace('/-/', '_', $request->getPreferredLanguage()));

                if (preg_match('/^pl_?.*/', $locale)) {
                    $locale = 'pl';
                } elseif (preg_match('/^ru_?.*/', $locale)) {
                    $locale = 'ru';
                } elseif (preg_match('/^de_?.*/', $locale)) {
                    $locale = 'de';
                } elseif (preg_match('/^es_?.*/', $locale)) {
                    $locale = 'es';
                } elseif (preg_match('/^fr_?.*/', $locale)) {
                    $locale = 'fr';
                } elseif (preg_match('/^pt_?.*/', $locale)) {
                    $locale = 'pt';
                } elseif (preg_match('/^it_?.*/', $locale)) {
                    $locale = 'it';
                } else {
                    $locale = 'en';
                }

                $request->getSession()->set('_locale', $locale);
            }

            $request->setLocale($locale);
        }
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
