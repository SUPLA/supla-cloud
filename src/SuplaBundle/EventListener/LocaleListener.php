<?php
/*
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface {
    private $defaultLocale;

    const SESSION_LOCALE_KEY = '_locale';

    public function __construct($defaultLocale) {
        $this->defaultLocale = $defaultLocale;
    }
    
    public static function localeAllowed($locale) {
    	return ($locale && in_array($locale, ['en', 'pl', 'de', 'ru', 'it', 'pt', 'es', 'fr'])) ? $locale : false;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        if ($desiredLocale = $request->get('lang')) {
            $this->rememberLocale($request, $desiredLocale);
            if ($request->getMethod() == 'GET') {
                $uriWithoutLang = rtrim(str_replace('lang=' . $desiredLocale, '', $request->getUri()), '?');
                $event->setResponse(new RedirectResponse($uriWithoutLang));
                return;
            }
        }

        if (!$this->hasRememberedLocale($request)) {
            $desiredLocale = strtolower(substr($request->getPreferredLanguage(), 0, 2));
            $this->rememberLocale($request, $desiredLocale);
        }

        $request->setLocale($this->getRememberedLocale($request));
    }

    private function rememberLocale(Request $request, string $locale) {
    	if (LocaleListener::localeAllowed($locale)) { // TODO autodiscover them?
            $request->getSession()->set(self::SESSION_LOCALE_KEY, $locale);
        }
    }

    private function hasRememberedLocale(Request $request): bool {
        return $request->getSession()->has(self::SESSION_LOCALE_KEY);
    }

    private function getRememberedLocale(Request $request): string {
        return $request->getSession()->get(self::SESSION_LOCALE_KEY, $this->defaultLocale);
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 15]],
        ];
    }
}
