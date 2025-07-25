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
 *   label = @Translation("User Vote"),
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
 *   },
 *   links = {
 *     "canonical" = "/api/simple-vote/simple_vote_user_vote/{simple_vote_user_vote}",
 *     "collection" = "/api/simple-vote/simple_vote_user_vote"
 *   }
 * )
 */

class SimpleVoteUserVote extends ContentEntityBase implements EntityOwnerInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Usuário'))
      ->setDescription(t('Referência ao usuário autenticado que votou.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(FALSE);

    $fields['anonymous_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Identificador Anônimo'))
      ->setDescription(t('Um identificador único (cookie ou fingerprint) para usuários não autenticados.'))
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

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Pergunta'))
      ->setDescription(t('Pergunta relacionada ao voto.'))
      ->setSetting('target_type', 'simple_vote_question')
      ->setRequired(TRUE);

    $fields['answer_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Resposta'))
      ->setDescription(t('Resposta selecionada pelo usuário.'))
      ->setSetting('target_type', 'simple_vote_answer')
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Criado em'));

    return $fields;
  }

  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  public function getOwner(): UserInterface {
    return $this->get('uid')->entity;
  }

  public function getOwnerId(): int {
    return $this->get('uid')->target_id ?? 0;
  }

  public function setOwnerId($uid): static {
    $this->set('uid', $uid);
    return $this;
  }

  public function setOwner(UserInterface $account): static {
    $this->set('uid', $account->id());
    return $this;
  }

}
