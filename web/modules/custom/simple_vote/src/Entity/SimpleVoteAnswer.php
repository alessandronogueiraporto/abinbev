<?php

namespace Drupal\simple_vote\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;

/**
 * Defines the SimpleVoteAnswer entity.
 *
 * @ContentEntityType(
 *   id = "simple_vote_answer",
 *   label = @Translation("Simple Vote - Answers"),
 *   base_table = "simple_vote_answer",
 *   admin_permission = "administer simple vote",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "published" = "status"
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   links = {
 *     "canonical" = "/api/simple-vote/answer/{simple_vote_answer}"
 *   }
 * )
 */
class SimpleVoteAnswer extends ContentEntityBase implements EntityPublishedInterface {
  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Answer title'))
      ->setRequired(TRUE)
      ->setSettings(['max_length' => 255])
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', ['type' => 'text_textarea', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setSettings([
        'file_directory' => 'public://simple_vote_answers',
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

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Related question'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'simple_vote_question')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => 3])
      ->setDisplayConfigurable('form', TRUE);

    $fields['vote_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Total votes'))
      ->setDefaultValue(0)
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 4])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

}
