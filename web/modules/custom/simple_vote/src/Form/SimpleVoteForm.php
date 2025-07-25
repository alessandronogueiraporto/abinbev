<?php

namespace Drupal\simple_vote\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_vote\Entity\SimpleVoteUserVote;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SimpleVoteForm extends FormBase {

  /**
   * Question for voting.
   *
   * @var \Drupal\simple_vote\Entity\SimpleVoteQuestion
   */
  protected $question;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Messenger Service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  public function __construct(AccountInterface $currentUser, MessengerInterface $messenger) {
    $this->currentUser = $currentUser;
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('messenger')
    );
  }

  public function getFormId() {
    return 'simple_vote_vote_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $question = NULL) {
    if (!$question) {
      $form['#markup'] = $this->t('Pergunta não encontrada.');
      return $form;
    }

    $this->question = $question;

    $answer_storage = \Drupal::entityTypeManager()->getStorage('simple_vote_answer');
    $answer_ids = $answer_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('question_id', $this->question->id())
      ->accessCheck(TRUE)
      ->execute();

    $answers = $answer_storage->loadMultiple($answer_ids);

    $options = [];
    foreach ($answers as $answer) {
      $options[$answer->id()] = $answer->label();
    }

    $show_results = $this->question->get('show_results')->value ?? TRUE;
    if ($show_results) {
      $vote_storage = \Drupal::entityTypeManager()->getStorage('simple_vote_user_vote');
      $total_votes = $vote_storage->getQuery()
        ->condition('question_id', $this->question->id())
        ->accessCheck(TRUE)
        ->count()
        ->execute();
        $form['total_votes'] = [
          '#markup' => $this->t('Total de votos: @count', ['@count' => $total_votes]),
          '#weight' => 10,
        ];
    }

    $form['answer'] = [
      '#type' => 'radios',
      '#title' => $this->t('Escolha uma resposta'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Votar'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $answer_id = $form_state->getValue('answer');

    $storage = \Drupal::entityTypeManager()->getStorage('simple_vote_user_vote');
    $query = $storage->getQuery()
    ->accessCheck(TRUE);

    if ($this->currentUser->isAuthenticated()) {
      $query->condition('uid', $this->currentUser->id());
    }
    else {
      $ip = \Drupal::request()->getClientIp();
      $query->condition('anonymous_id', $ip);
    }

    $query->condition('question_id', $this->question->id());
    $existing = $query->execute();

    if (!empty($existing)) {
      $form_state->setErrorByName('answer', $this->t('Você já votou nessa pergunta.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $answer_id = $form_state->getValue('answer');

    $vote_storage = \Drupal::entityTypeManager()->getStorage('simple_vote_user_vote');

    $vote = $vote_storage->create([
      'question_id' => $this->question->id(),
      'answer_id' => $answer_id,
      'created' => \Drupal::time()->getRequestTime(),
    ]);

    if ($this->currentUser->isAuthenticated()) {
      $vote->set('uid', $this->currentUser->id());
    }
    else {
      $ip = \Drupal::request()->getClientIp();
      $vote->set('anonymous_id', $ip);
    }

    $vote->save();

    $this->messenger->addMessage($this->t('Voto registrado com sucesso!'));
    $form_state->setRedirect('<current>');
  }
}
