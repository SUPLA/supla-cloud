<?php

namespace SuplaBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseContentLengthListener {
    public function __invoke(ResponseEvent $event) {
        if (strpos($event->getRequest()->getPathInfo(), '/direct/') === 0) {
            $response = $event->getResponse();
            $content = $response->getContent();
            if ($content[0] === '{') {
                // fix for malfunction clients on arduino that read direct links responses
                // @see https://forum.supla.org/viewtopic.php?t=12156
                $response->headers->set('Content-Length', mb_strlen($content));
            }
        }
    }
}
