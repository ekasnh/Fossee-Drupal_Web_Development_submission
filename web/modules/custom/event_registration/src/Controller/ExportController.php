<?php
namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * Provides a CSV export for event registrations.
 */
class ExportController extends ControllerBase {

  /**
   * Generates a CSV file of registrations for a specific event.
   */
  public function export($event_id) {
    // 1. Fetch event name for the filename.
    $event = \Drupal::database()->select('event_configs', 'e')
      ->fields('e', ['event_name'])
      ->condition('id', $event_id)
      ->execute()
      ->fetchObject();
    
    $filename = $event ? preg_replace('/[^a-z0-9]/i', '_', $event->event_name) . '_registrations.csv' : 'registrations.csv';

    // 2. Query all registrations for this event.
    $query = \Drupal::database()->select('event_registrations', 'r');
    $query->fields('r')
      ->condition('event_id', $event_id)
      ->orderBy('created', 'DESC');
    $results = $query->execute();

    // 3. Create the CSV content in memory.
    $handle = fopen('php://temp', 'w+');
    
    // Add the Header Row.
    fputcsv($handle, [
      $this->t('Full Name'),
      $this->t('Email'),
      $this->t('College Name'),
      $this->t('Department'),
      $this->t('Registration Date'),
    ]);

    // Add Data Rows.
    while ($row = $results->fetchAssoc()) {
      fputcsv($handle, [
        $row['full_name'],
        $row['email'],
        $row['college'],
        $row['department'],
        date('Y-m-d H:i:s', $row['created']),
      ]);
    }

    rewind($handle);
    $csv_data = stream_get_contents($handle);
    fclose($handle);

    // 4. Return the response as a downloadable file.
    $response = new Response($csv_data);
    $disposition = HeaderUtils::makeDisposition(
      HeaderUtils::DISPOSITION_ATTACHMENT,
      $filename
    );
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', $disposition);

    return $response;
  }
}