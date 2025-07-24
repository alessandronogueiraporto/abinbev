<?php

namespace Drupal\simple_vote\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

class SimpleVoteRestAccessSubscriber implements EventSubscriberInterface {

  protected $configFactory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onRequest', 100],
    ];
  }

  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    $config = $this->configFactory->get('simple_vote.settings');
    if (!$config->get('enabled')) {
      $restricted_paths = [
        '/api/simple-vote-questions',
        '/entity/simple_vote_question',
        '/entity/simple_vote_answer',
        '/entity/simple_vote_user_vote',
      ];

      foreach ($restricted_paths as $prefix) {
        if (strpos($path, $prefix) === 0) {
          $response = new Response('Voting system is disabled.', 403);
          $event->setResponse($response);
          return;
        }
      }
    }
  }
}
