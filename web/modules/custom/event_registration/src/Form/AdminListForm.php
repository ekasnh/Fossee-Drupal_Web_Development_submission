<?php
namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AdminListForm extends FormBase {
  public function getFormId() { return 'event_registration_admin_list'; }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // 1. Date Dropdown
    $dates = \Drupal::database()->select('event_configs', 'e')
      ->fields('e', ['event_date'])->distinct()->execute()->fetchCol();
    
    $form['filter_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter by Event Date'),
      '#options' => ['' => '- Select Date -'] + array_combine($dates, $dates),
      '#ajax' => [
        'callback' => '::updateAdminTableCallback',
        'wrapper' => 'admin-ajax-wrapper',
      ],
    ];

    $form['admin_ajax_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'admin-ajax-wrapper'],
    ];

    $selected_date = $form_state->getValue('filter_date');

    if ($selected_date) {
      // 2. Event Name Dropdown (Dependent on Date)
      $events = \Drupal::database()->select('event_configs', 'e')
        ->fields('e', ['id', 'event_name'])
        ->condition('event_date', $selected_date)
        ->execute()->fetchAllKeyed();

      $form['admin_ajax_wrapper']['event_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Event Name'),
        '#options' => ['' => '- Select Event -'] + $events,
        '#ajax' => [
          'callback' => '::updateAdminTableCallback',
          'wrapper' => 'admin-ajax-wrapper',
        ],
      ];

      $selected_event = $form_state->getValue('event_id');
      if ($selected_event) {
        $query = \Drupal::database()->select('event_registrations', 'r');
        $query->fields('r')->condition('event_id', $selected_event);
        $results = $query->execute()->fetchAll();

        $form['admin_ajax_wrapper']['stats'] = [
          '#markup' => '<h4>Total Participants: ' . count($results) . '</h4>',
        ];

        $header = ['Name', 'Email', 'Event Date', 'College', 'Department', 'Submission Date'];
        $rows = [];
        foreach ($results as $row) {
          $rows[] = [
            $row->full_name,
            $row->email,
            $selected_date,
            $row->college,
            $row->department,
            date('Y-m-d', $row->created),
          ];
        }

        $form['admin_ajax_wrapper']['table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#empty' => $this->t('No participants found.'),
        ];

        $form['admin_ajax_wrapper']['export'] = [
          '#type' => 'link',
          '#title' => $this->t('Export as CSV'),
          '#url' => Url::fromRoute('event_registration.export_csv', ['event_id' => $selected_event]),
          '#attributes' => ['class' => ['button', 'button--primary']],
        ];
      }
    }

    return $form;
  }

  public function updateAdminTableCallback(array &$form, FormStateInterface $form_state) {
    return $form['admin_ajax_wrapper'];
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {}
}