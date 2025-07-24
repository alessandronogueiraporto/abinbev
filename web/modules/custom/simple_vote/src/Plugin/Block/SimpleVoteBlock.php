<?php

namespace Drupal\simple_vote\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\simple_vote\Entity\SimpleVoteQuestion;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\simple_vote\Form\SimpleVoteForm;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Simple Vote' block.
 *
 * @Block(
 *   id = "simple_vote_block",
 *   admin_label = @Translation("Simple Vote Block"),
 * )
 */
class SimpleVoteBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;
  protected $formBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  public function build() {
    $config = \Drupal::config('simple_vote.settings');
    if (!$config->get('enabled')) {
      return [];
    }

    $block_config = $this->getConfiguration();
    if (!empty($block_config['question_id']) && $question = SimpleVoteQuestion::load($block_config['question_id'])) {
      return [
        'question_title' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $question->label(),
        ],
        'vote_form' => $this->formBuilder->getForm(SimpleVoteForm::class, $question),
      ];
    }

    return [
      '#markup' => $this->t('No questions selected.'),
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $options = [];
    $questions = SimpleVoteQuestion::loadMultiple();
    foreach ($questions as $question) {
      $options[$question->id()] = $question->label();
    }

    $form['question_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Pergunta a ser exibida'),
      '#options' => $options,
      '#default_value' => $this->configuration['question_id'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['question_id'] = $form_state->getValue('question_id');
  }

  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $config = \Drupal::config('simple_vote.settings');
    $enabled = $config->get('enabled') ?? FALSE;
    $access_result = $enabled ? AccessResult::allowed() : AccessResult::forbidden();
    return $return_as_object ? $access_result : $access_result->isAllowed();
  }
}
