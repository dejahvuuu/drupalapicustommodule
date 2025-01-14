<?php

namespace Drupal\safetti_custom\Service;

use Drupal\Core\Database\Connection;

/**
 * Service class to handle CRUD operations on safetti_custom_data table.
 */
class SafettiCustomService {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public function getAllData() {
    return $this->database->select('safetti_custom_data', 'c')
      ->fields('c')
      ->execute()
      ->fetchAll();
  }

  public function insertData($data) {
    $this->database->insert('safetti_custom_data')
      ->fields([
        'nid' => $data['nid'],
        'scenario_id' => $data['scenario_id'],
        'created_at' => date('Y-m-d H:i:s'),
      ])
      ->execute();
    return ['message' => 'Data inserted successfully'];
  }

  public function updateData($id, $data) {
    $this->database->update('safetti_custom_data')
      ->fields([
        'scenario_id' => $data['scenario_id'],
      ])
      ->condition('nid', $id)
      ->execute();
    return ['message' => "Data with ID $id updated successfully"];
  }

  public function deleteData($id) {
    $this->database->delete('safetti_custom_data')
      ->condition('nid', $id)
      ->execute();
    return ['message' => "Data with ID $id deleted successfully"];
  }
}