<?php
namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RegistrationForm extends FormBase {

  public function getFormId() {
    return 'event_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Standard Fields (Always Visible)
    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['college_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
    ];

    // Category Dropdown (Triggers AJAX)
    $categories = \Drupal::database()->select('event_configs', 'e')
      ->fields('e', ['category'])->distinct()->execute()->fetchCol();

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#options' => ['' => '- Select Category -'] + array_combine($categories, $categories),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateAjaxCallback',
        'wrapper' => 'ajax-wrapper',
      ],
    ];

    // AJAX Wrapper for Dependent Fields
    $form['ajax_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax-wrapper'],
    ];

    $selected_cat = $form_state->getValue('category');

    if ($selected_cat) {
      $dates = \Drupal::database()->select('event_configs', 'e')
        ->fields('e', ['event_date'])
        ->condition('category', $selected_cat)
        ->execute()->fetchCol();

      $form['ajax_wrapper']['event_date'] = [
        '#type' => 'select',
        '#title' => $this->t('Event Date'),
        '#options' => ['' => '- Select Date -'] + array_combine($dates, $dates),
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::updateAjaxCallback',
          'wrapper' => 'ajax-wrapper',
        ],
      ];

      $selected_date = $form_state->getValue('event_date');
      if ($selected_date) {
        $events = \Drupal::database()->select('event_configs', 'e')
          ->fields('e', ['id', 'event_name'])
          ->condition('category', $selected_cat)
          ->condition('event_date', $selected_date)
          ->execute()->fetchAllKeyed();

        $form['ajax_wrapper']['event_id'] = [
          '#type' => 'select',
          '#title' => $this->t('Event Name'),
          '#options' => $events,
          '#required' => TRUE,
        ];
      }
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function updateAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['ajax_wrapper'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $fields = ['full_name', 'college_name', 'department'];
    foreach ($fields as $field) {
      if (preg_match('/[^a-zA-Z0-9\s]/', $form_state->getValue($field))) {
        $form_state->setErrorByName($field, $this->t('Special characters are not allowed.'));
      }
    }

    $email = $form_state->getValue('email');
    $date = $form_state->getValue('event_date');
    
    // Check for duplicates
    if ($date) {
        $query = \Drupal::database()->select('event_registrations', 'r');
        $query->join('event_configs', 'c', 'r.event_id = c.id');
        $duplicate = $query->fields('r', ['id'])
          ->condition('r.email', $email)
          ->condition('c.event_date', $date)
          ->execute()->fetchField();

        if ($duplicate) {
          $form_state->setErrorByName('email', $this->t('You are already registered for an event on this date.'));
        }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // 1. Save to Database (CRITICAL FIX: Insert data before mailing)
    \Drupal::database()->insert('event_registrations')->fields([
        'event_id' => $values['event_id'],
        'full_name' => $values['full_name'],
        'email' => $values['email'],
        'college' => $values['college_name'],
        'department' => $values['department'],
        'created' => time(),
    ])->execute();

    // 2. Fetch the Event Name for the email
    $event_details = \Drupal::database()->select('event_configs', 'e')
      ->fields('e', ['event_name'])
      ->condition('id', $values['event_id'])
      ->execute()
      ->fetchObject();

    // 3. Prepare the email parameters
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'event_registration';
    $key = 'registration_confirmation';
    $to = $values['email'];
    $params = [
      'full_name' => $values['full_name'],
      'event_name' => $event_details->event_name ?? 'The Event',
      'category' => $values['category'],
      'event_date' => $values['event_date'],
    ];
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // 4. Send the email
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, TRUE);

    if ($result['result'] === TRUE) {
      \Drupal::messenger()->addStatus($this->t('Registration successful! Confirmation sent to Mailpit.'));
    } else {
      \Drupal::messenger()->addStatus($this->t('Registration saved, but email could not be sent.'));
    }
    // Inside submitForm() in RegistrationForm.php
    $config = \Drupal::config('event_registration.settings');
    if ($config->get('notify_admin')) {
    $admin_email = $config->get('admin_email');
    if (!empty($admin_email)) {
        $mailManager->mail($module, 'admin_notification', $admin_email, $langcode, $params, NULL, TRUE);
    }
    }
  }
}