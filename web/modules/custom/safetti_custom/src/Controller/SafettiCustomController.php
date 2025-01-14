<?php

namespace Drupal\safetti_custom\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\safetti_custom\Service\SafettiCustomService;
use Drupal\safetti_custom\Service\JwtValidator;
use Drupal\safetti_custom\Validator\SafettiCustomValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 * Provides CRUD API for custom data.
 */
class SafettiCustomController extends ControllerBase {

  /**
   * The CRUD service.
   *
   * @var \Drupal\safetti_custom\Service\SafettiCustomService
   */
  protected $crudService;

  /**
   * The JWT validator service.
   *
   * @var \Drupal\safetti_custom\Service\JwtValidator
   */
  protected $jwtValidator;

  /**
   * The data validator 
   *
   * @var \Drupal\safetti_custom\Validator\SafettiCustomValidator
   */
  protected $dataValidator;  

  /**
   * Constructs a SafettiCustomController object.
   *
   * @param \Drupal\safetti_custom\Service\SafettiCustomService $crudService
   *   The custom CRUD service.
   * @param \Drupal\safetti_custom\Service\JwtValidator $jwtValidator
   *   The JWT validator service.
   */
  public function __construct(SafettiCustomService $crudService, JwtValidator $jwtValidator, SafettiCustomValidator $dataValidator) {
    $this->crudService = $crudService;
    $this->jwtValidator = $jwtValidator;
    $this->dataValidator = $dataValidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('safetti_custom.service'),
      $container->get('safetti_custom.jwt_validator'),
      $container->get('safetti_custom.data_validator')
    );
  }

  /**
   * Endpoint to generate a JWT token.
   */
  public function getToken(Request $request) {
    $token = $this->jwtValidator->generateToken([
      'sub' => 'user_id_123',
      'role' => 'admin',
    ]);

    return new JsonResponse($token);
  }
  
  public function refreshToken(Request $request) {
    $refreshToken = $request->headers->get('Authorization');
    if (!$refreshToken || strpos($refreshToken, 'Bearer ') !== 0) {
      return new JsonResponse(['message' => 'Refresh token missing or invalid.'], 401);
    }
  
    $refreshToken = substr($refreshToken, 7);
  
    try {
      // Parsear el token
      $jwtToken = $this->jwtValidator->parseToken($refreshToken);
  
      // Validar que el token no haya expirado
      if ($this->jwtValidator->isExpired($jwtToken)) {
        return new JsonResponse(['message' => 'Refresh token has expired.'], 401);
      }
  
      $claims = $this->jwtValidator->getClaims($jwtToken);
  
      if (!isset($claims['data']) || !is_array($claims['data'])) {
        return new JsonResponse([
          'message' => 'Invalid token claims. The "data" claim is missing or not valid.',
          'claims_received' => $claims
        ], 401);
      }
  
      $newTokens = $this->jwtValidator->generateToken($claims['data']);
  
      return new JsonResponse($newTokens);
    }
    catch (\Exception $e) {
      return new JsonResponse(['message' => 'Invalid refresh token.'], 401);
    }
  }
  
  /**
   * Handles GET requests to fetch data.
   */
  public function getData(Request $request) {
    if (!$this->jwtValidator->validate($request)) {
      return new JsonResponse(['message' => 'Unauthorized'], 401);
    }

    $data = $this->crudService->getAllData();
    return new JsonResponse($data);
  }

  /**
   * Handles POST requests to insert data.
   */
  public function postData(Request $request) {
    if (!$this->jwtValidator->validate($request)) {
      return new JsonResponse(['message' => 'Unauthorized'], 401);
    }

    $data = json_decode($request->getContent(), TRUE);
    $validationResult = $this->dataValidator->validate($data);

    if ($validationResult !== TRUE) {
      return $validationResult;
    }

    $result = $this->crudService->insertData($data);
    return new JsonResponse($result);
  }

  /**
   * Handles PUT requests to update data.
   */
  public function putData($id, Request $request) {
    if (!$this->jwtValidator->validate($request)) {
      return new JsonResponse(['message' => 'Unauthorized'], 401);
    }

    $data = json_decode($request->getContent(), TRUE);
    $result = $this->crudService->updateData($id, $data);
    return new JsonResponse($result);
  }

  /**
   * Handles DELETE requests to delete data.
   */
  public function deleteData($id, Request $request) {
    if (!$this->jwtValidator->validate($request)) {
      return new JsonResponse(['message' => 'Unauthorized'], 401);
    }

    $result = $this->crudService->deleteData($id);
    return new JsonResponse($result);
  }
}
