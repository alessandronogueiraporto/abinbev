<?php

namespace Drupal\simple_vote\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SimpleVoteSettingsForm extends ConfigFormBase {

  const SETTINGS = 'simple_vote.settings';

  public function getFormId() {
    return 'simple_vote_settings_form';
  }

  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable voting system'),
      '#default_value' => $config->get('enabled'),
      '#description' => $this->t('Check to enable the voting system. Uncheck to disable.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('enabled', $form_state->getValue('enabled'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
