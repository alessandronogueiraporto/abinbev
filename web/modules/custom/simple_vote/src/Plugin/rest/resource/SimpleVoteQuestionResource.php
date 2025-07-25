<?php

namespace Drupal\simple_vote\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Annotation\RestResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a REST API to return Questions and their Answers with vote counts.
 *
 * @RestResource(
 *   id = "simple_vote_question_resource",
 *   label = @Translation("Simple Vote - Questions and Answers"),
 *   uri_paths = {
 *     "canonical" = "/api/simple-vote/question-answer"
 *   }
 * )
 */
class SimpleVoteQuestionResource extends ResourceBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new SimpleVoteQuestionResource object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
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

  public function get(Request $request) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $questionStorage = $this->entityTypeManager->getStorage('simple_vote_question');
    $answerStorage = $this->entityTypeManager->getStorage('simple_vote_answer');
    $voteStorage = $this->entityTypeManager->getStorage('simple_vote_user_vote');

    $id = $request->query->get('id');
    $data = [];

    if ($id) {
      $question = $questionStorage->load($id);

      if (!$question || !$question->isPublished()) {
        return new ResourceResponse(['message' => 'Question not found or unpublished'], 404);
      }

      $data[] = $this->buildQuestionData($question, $answerStorage, $voteStorage);
    }
    else {
      $page = (int) $request->query->get('page', 0);
      $limit = (int) $request->query->get('limit', 2);
      $limit = max(1, min($limit, 50));

      $query = $questionStorage->getQuery()
        ->condition('status', 1)
        ->accessCheck(TRUE)
        ->range($page * $limit, $limit);

      $questionIds = $query->execute();
      $questions = $questionStorage->loadMultiple($questionIds);

      foreach ($questions as $question) {
        $data[] = $this->buildQuestionData($question, $answerStorage, $voteStorage);
      }
    }

    return new ResourceResponse($data);
  }

  protected function buildQuestionData($question, $answerStorage, $voteStorage) {
    $questionData = [
      'id' => $question->id(),
      'title' => $question->label(),
      'machine_name' => $question->get('machine_name')->value,
    ];

    $answerIds = $answerStorage->getQuery()
      ->condition('question_id', $question->id())
      ->accessCheck(TRUE)
      ->execute();

    $answers = $answerStorage->loadMultiple($answerIds);
    $answersData = [];

    foreach ($answers as $answer) {
      $voteCount = $voteStorage->getQuery()
        ->condition('answer_id', $answer->id())
        ->accessCheck(TRUE)
        ->count()
        ->execute();

      $answersData[] = [
        'id' => $answer->id(),
        'title' => $answer->label(),
        'description' => $answer->get('description')->value,
        'vote_count' => (int) $voteCount,
      ];
    }

    $questionData['answers'] = $answersData;
    return $questionData;
  }

}
