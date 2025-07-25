<?php

namespace Drupal\simple_vote\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Annotation\RestResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @RestResource(
 *   id = "simple_vote_question_resource",
 *   label = @Translation("Simple Vote - Questions and Answers"),
 *   uri_paths = {
 *     "canonical" = "/api/simple-vote/question-answer"
 *   }
 * )
 */

class SimpleVoteQuestionResource extends ResourceBase implements ContainerFactoryPluginInterface {
  protected $entityTypeManager;
  protected $currentUser;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('simple_vote'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  public function get() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $question_storage = $this->entityTypeManager->getStorage('simple_vote_question');
    $answer_storage = $this->entityTypeManager->getStorage('simple_vote_answer');
    $vote_storage = $this->entityTypeManager->getStorage('simple_vote_user_vote');

    $question_ids = $question_storage->getQuery()
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->execute();

    $questions = $question_storage->loadMultiple($question_ids);
    $data = [];

    foreach ($questions as $question) {
      $question_data = [
        'id' => $question->id(),
        'title' => $question->label(),
        'machine_name' => $question->get('machine_name')->value,
      ];

      $answer_ids = $answer_storage->getQuery()
        ->condition('question_id', $question->id())
        ->accessCheck(TRUE)
        ->execute();

      $answer_entities = $answer_storage->loadMultiple($answer_ids);

      $answers_data = [];
      foreach ($answer_entities as $answer) {
        $vote_count = $vote_storage->getQuery()
          ->condition('answer_id', $answer->id())
          ->accessCheck(TRUE)
          ->count()
          ->execute();

        $answers_data[] = [
          'id' => $answer->id(),
          'title' => $answer->label(),
          'description' => $answer->get('description')->value,
          'vote_count' => (int) $vote_count,
        ];
      }

      $question_data['answers'] = $answers_data;
      $data[] = $question_data;
    }

    return new ResourceResponse($data);
  }

}
