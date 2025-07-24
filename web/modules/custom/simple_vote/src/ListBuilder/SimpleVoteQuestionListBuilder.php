<?php
namespace Drupal\simple_vote\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class SimpleVoteQuestionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = \Drupal\Core\Link::createFromRoute(
      $entity->label(),
      'entity.simple_vote_question.canonical',
      ['simple_vote_question' => $entity->id()]
    );

    $row['edit'] = \Drupal\Core\Link::createFromRoute(
      $this->t('Edit'),
      'entity.simple_vote_question.edit_form',
      ['simple_vote_question' => $entity->id()]
    );

    $row['delete'] = \Drupal\Core\Link::createFromRoute(
      $this->t('Delete'),
      'entity.simple_vote_question.delete_form',
      ['simple_vote_question' => $entity->id()]
    );

    return $row;
  }

  /**
   * {@inheritdoc}
   *
   * Removes the "Operations" column from the table.
   */
  public function render() {
    $build['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Questions'),
        $this->t('Edit'),
        $this->t('Delete'),
      ],
      '#rows' => [],
      '#empty' => $this->t('No questions found.'),
    ];

    foreach ($this->load() as $entity) {
      $build['table']['#rows'][] = $this->buildRow($entity);
    }

    return $build;
  }

}
