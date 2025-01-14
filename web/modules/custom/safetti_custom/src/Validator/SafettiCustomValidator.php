<?php

namespace Drupal\safetti_custom\Validator;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides validation for incoming data in the API requests.
 */
class SafettiCustomValidator {

  /**
   * Validates the incoming data for POST and PUT requests.
   *
   * @param array $data
   *   The data to validate.
   *
   * @return JsonResponse|bool
   *   Returns TRUE if the data is valid, or a JsonResponse with the error message.
   */
  public function validate(array $data) {
    if (empty($data['nid'])) {
      return new JsonResponse(['error' => 'The "nid" field is required.'], 400);
    }

    if (empty($data['scenario_id'])) {
      return new JsonResponse(['error' => 'The "scenario_id" field is required.'], 400);
    }

    return TRUE;
  }
}
