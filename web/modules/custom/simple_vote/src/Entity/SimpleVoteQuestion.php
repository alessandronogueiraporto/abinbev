<?php

namespace Drupal\simple_vote\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Defines the SimpleVoteQuestion entity.
 *
 * @ContentEntityType(
 *   id = "simple_vote_question",
 *   label = @Translation("Simple Vote - Question"),
 *   base_table = "simple_vote_question",
 *   admin_permission = "administer simple vote",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "published" = "status"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\simple_vote\SimpleVoteQuestionViewBuilder",
 *     "list_builder" = "Drupal\simple_vote\ListBuilder\SimpleVoteQuestionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\simple_vote\Form\SimpleVoteQuestionForm",
 *       "edit" = "Drupal\simple_vote\Form\SimpleVoteQuestionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData"
 *   },
 *   route_provider = {
 *     "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     "rest" = "Drupal\rest\Plugin\RestEntityRouteProvider"
 *   },
 *   links = {
 *     "canonical" = "/api/simple-vote/question/{simple_vote_question}",
 *     "add-form" = "/api/simple-vote/question/add",
 *     "edit-form" = "/api/simple-vote/question/{simple_vote_question}/edit",
 *     "delete-form" = "/api/simple-vote/question/{simple_vote_question}/delete",
 *     "collection" = "/api/simple-vote/question"
 *   },
 *   field_ui_base_route = "entity.simple_vote_question.settings"
 * )
 */

class SimpleVoteQuestion extends ContentEntityBase implements EntityPublishedInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['machine_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Machine Name'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 128,
        'is_ascii' => TRUE,
        'case_sensitive' => FALSE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['show_results'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show results after voting'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

  /**
   * Returns the current user ID as default author.
   */
  public static function getCurrentUserId() {
    return \Drupal::currentUser()->id();
  }

}
