<?php
namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class EventConfigForm extends FormBase {
  public function getFormId() { return 'event_config_form'; }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
    ];
    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => [
        'Online Workshop' => 'Online Workshop',
        'Hackathon' => 'Hackathon',
        'Conference' => 'Conference',
        'One-day Workshop' => 'One-day Workshop',
      ],
      '#required' => TRUE,
    ];
    $form['reg_start'] = ['#type' => 'date', '#title' => $this->t('Registration Start'), '#required' => TRUE];
    $form['reg_end'] = ['#type' => 'date', '#title' => $this->t('Registration End'), '#required' => TRUE];
    $form['event_date'] = ['#type' => 'date', '#title' => $this->t('Event Date'), '#required' => TRUE];

    $form['submit'] = ['#type' => 'submit', '#value' => $this->t('Save Event')];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::database()->insert('event_configs')->fields([
      'event_name' => $form_state->getValue('event_name'),
      'category' => $form_state->getValue('category'),
      'reg_start' => $form_state->getValue('reg_start'),
      'reg_end' => $form_state->getValue('reg_end'),
      'event_date' => $form_state->getValue('event_date'),
    ])->execute();
    \Drupal::messenger()->addStatus($this->t('Event saved successfully.'));
  }
}