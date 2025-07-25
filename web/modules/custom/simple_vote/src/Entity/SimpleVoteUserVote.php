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

  /**
   * Define os campos da entidade.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    // UID do usuário logado.
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

    // Identificador para usuários anônimos.
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

    // Pergunta associada.
    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Pergunta'))
      ->setDescription(t('Pergunta relacionada ao voto.'))
      ->setSetting('target_type', 'simple_vote_question')
      ->setRequired(TRUE);

    // Resposta escolhida.
    $fields['answer_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Resposta'))
      ->setDescription(t('Resposta selecionada pelo usuário.'))
      ->setSetting('target_type', 'simple_vote_answer')
      ->setRequired(TRUE);

    // Data de criação.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Criado em'));

    return $fields;
  }

  /**
   * Define valor padrão do campo uid para usuários autenticados.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Implementa EntityOwnerInterface::getOwner().
   */
  public function getOwner(): UserInterface {
    return $this->get('uid')->entity;
  }

  /**
   * Implementa EntityOwnerInterface::getOwnerId().
   */
  public function getOwnerId(): int {
    return $this->get('uid')->target_id ?? 0;
  }

  /**
   * Implementa EntityOwnerInterface::setOwnerId().
   */
  public function setOwnerId($uid): static {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * Implementa EntityOwnerInterface::setOwner().
   */
  public function setOwner(UserInterface $account): static {
    $this->set('uid', $account->id());
    return $this;
  }

}
