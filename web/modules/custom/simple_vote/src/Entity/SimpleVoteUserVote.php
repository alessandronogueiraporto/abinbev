<?php

namespace Drupal\simple_vote\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Defines the User Vote entity.
 *
 * @ContentEntityType(
 *   id = "simple_vote_user_vote",
 *   label = @Translation("Simple Vote - User Vote"),
 *   base_table = "simple_vote_user_vote",
 *   admin_permission = "administer simple vote",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "uid"
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder"
 *   }
 * )
 */
class SimpleVoteUserVote extends ContentEntityBase implements EntityOwnerInterface {

  /**
   * Defines the entity fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    // UID of the logged in user.
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('Reference to the authenticated user who voted.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(FALSE);

    // Identifier for anonymous users.
    $fields['anonymous_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Anonymous Identifier'))
      ->setDescription(t('A unique identifier (fingerprint) for unauthenticated users.'))
      ->setSettings([
        'max_length' => 128,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(FALSE);

    // Associated question.
    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setDescription(t('Voting related question.'))
      ->setSetting('target_type', 'simple_vote_question')
      ->setRequired(TRUE);

    // Chosen answer.
    $fields['answer_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Response'))
      ->setDescription(t('User selected answer.'))
      ->setSetting('target_type', 'simple_vote_answer')
      ->setRequired(TRUE);

    // Date of creation.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created in'));

    return $fields;
  }

  /**
   * Sets default value of uid field for authenticated users.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Implements EntityOwnerInterface::getOwner().
   */
  public function getOwner(): UserInterface {
    return $this->get('uid')->entity;
  }

  /**
   * Implements EntityOwnerInterface::getOwnerId().
   */
  public function getOwnerId(): int {
    return $this->get('uid')->target_id ?? 0;
  }

  /**
   * Implements EntityOwnerInterface::setOwnerId().
   */
  public function setOwnerId($uid): static {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * Implements EntityOwnerInterface::setOwner().
   */
  public function setOwner(UserInterface $account): static {
    $this->set('uid', $account->id());
    return $this;
  }

}
