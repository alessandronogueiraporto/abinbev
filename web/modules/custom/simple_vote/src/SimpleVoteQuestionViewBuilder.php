<?php

namespace Drupal\simple_vote;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

class SimpleVoteQuestionViewBuilder extends EntityViewBuilder {
  use StringTranslationTrait;

  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {

    $build = parent::view($entity, $view_mode, $langcode);

    $build['question_title'] = [
      '#markup' => '<h2>' . $entity->label() . '</h2>',
      '#weight' => -10,
    ];

    $status = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    $build['status'] = [
      '#markup' => '<p><strong>' . $this->t('Status') . ':</strong> ' . $status . '</p>',
      '#weight' => -9,
    ];

    $entity_type_manager = \Drupal::entityTypeManager();

    // Load associated responses
    $answer_storage = $entity_type_manager->getStorage('simple_vote_answer');
    $answer_ids = $answer_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('question_id', $entity->id())
      ->sort('created', 'ASC')
      ->execute();
    $answers = $answer_storage->loadMultiple($answer_ids);

    // Load votes
    $vote_storage = $entity_type_manager->getStorage('simple_vote_user_vote');
    $vote_ids = $vote_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('question_id', $entity->id())
      ->execute();
    $votes = $vote_storage->loadMultiple($vote_ids);

    $total_votes = count($votes);
    $votes_count_by_answer = [];
    foreach ($votes as $vote) {
      $answer_id = $vote->get('answer_id')->target_id;
      if (!isset($votes_count_by_answer[$answer_id])) {
        $votes_count_by_answer[$answer_id] = 0;
      }
      $votes_count_by_answer[$answer_id]++;
    }

    $rows = [];
    foreach ($answers as $answer) {
      $image_uri = NULL;
      if (!$answer->get('image')->isEmpty()) {
        $image = $answer->get('image')->entity;
        if ($image instanceof \Drupal\file\Entity\File) {
          $image_uri = $image->getFileUri();
        }
      }

      // Use base image or icon style
      if ($image_uri) {
        $image_url = ImageStyle::load('thumbnail')->buildUrl($image_uri);
      }
      else {
        $module_path = \Drupal::service('extension.list.module')->getPath('simple_vote');
  $image_url = base_path() . $module_path . '/img/icons/no-image.jpg';
      }

      $image_html = '<img src="' . $image_url . '" width="50" height="50" alt="' . $answer->label() . '" style="object-fit:cover;" />';

      $count = $votes_count_by_answer[$answer->id()] ?? 0;
      $percent = $total_votes > 0 ? round(($count / $total_votes) * 100, 2) : 0;

      $description = $answer->hasField('description') && !$answer->get('description')->isEmpty()
      ? $answer->get('description')->value
      : '-';

      $rows[] = [
        ['data' => ['#markup' => $image_html]],
        ['data' => $answer->label()],
        ['data' => ['#markup' => $description]],
        ['data' => $count],
        ['data' => $percent . ' %'],
      ];
    }

    $header = [
      $this->t('Image'),
      $this->t('Answer'),
      $this->t('Description'),
      $this->t('Votes'),
      $this->t('Percentage'),
    ];

    $build['voting_results_table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => ['class' => ['simple-vote-results-table']],
      '#weight' => 10,
    ];

    $build['#title'] = $this->t('Voting result');

    return $build;
  }
}
