<?php

namespace Drupal\simple_vote\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the SimpleVoteAnswer entity.
 *
 * @ContentEntityType(
 *   id = "simple_vote_answer",
 *   label = @Translation("Simple Vote - Answers"),
 *   base_table = "simple_vote_answer",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title"
 *   },
 *   admin_permission = "administer simple vote",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   links = {
 *     "canonical" = "/api/simple-vote/answers/{simple_vote_answer}",
 *   }
 * )
 */

class SimpleVoteAnswer extends ContentEntityBase {

  use EntityChangedTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Answer title
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Answer title'))
      ->setRequired(TRUE)
      ->setSettings(['max_length' => 255])
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Description
    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE);

    // Image associated with the answer
    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setSettings([
        'file_directory' => 'simple_vote_answers',
        'file_extensions' => 'jpg jpeg png svg',
        'alt_field' => FALSE,
        'title_field' => FALSE,
      ])
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 2,
        'settings' => [
          'preview_image_style' => 'thumbnail',
          'progress_indicator' => 'throbber',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Relation to the question
    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Related question'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'simple_vote_question')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => 3])
      ->setDisplayConfigurable('form', TRUE);

    // Total votes
    $fields['vote_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Total votes'))
      ->setDefaultValue(0)
      ->setReadOnly(TRUE);

    // Creation and modification dates
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created in'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed on'));

    return $fields;
  }

}
