<?php

// src/EventSubscriber/CustomRestResourceSubscriber.php

namespace Drupal\custom_api\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomRestResourceSubscriber implements EventSubscriberInterface {

  /**
   * Modifies REST resource responses.
   */
  public function onRespond(ViewEvent $event) {
    $request = $event->getRequest();
    $response = $event->getResponse();

    // Check if the request corresponds to the desired REST resource.
    echo "<pre>";
    print_r($request->attributes->get('_route'));
    die('----a----');
    if ($request->attributes->get('_route') == 'entity.node.canonical') {
      // Modify the response content as needed.
      $content = $response->getContent();
      // Modify $content here.
      $response->setContent($content);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::VIEW][] = ['onRespond'];
    return $events;
  }
}
