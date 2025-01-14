<?php

namespace Drupal\safetti_custom\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service to handle JWT validation and generation.
 */
class JwtValidator {

  private const SECRET_KEY = '146b9171cffae6f3d269aa8096bed2641b83e01c32ea73b99b95e82491242d36';

  protected $jwtConfig;

  /**
   * Constructs a JwtValidator object.
   */
  public function __construct() {

    $this->jwtConfig = Configuration::forSymmetricSigner(
      new \Lcobucci\JWT\Signer\Hmac\Sha256(),
      \Lcobucci\JWT\Signer\Key\InMemory::plainText(self::SECRET_KEY) // 🔑
    );

    $this->jwtConfig->setValidationConstraints(
      new SignedWith($this->jwtConfig->signer(), $this->jwtConfig->signingKey()) // 🔑
    );
  }

  /**
   * Generates a JWT token.
   */
  public function generateToken(array $claims) {

    $now = new \DateTimeImmutable();

    $accessToken = $this->jwtConfig->builder()
        ->issuedBy('http://localhost:8080') // TODO -> get string from config
        ->permittedFor('http://localhost:8080') // TODO -> get string from config
        ->issuedAt($now)
        ->expiresAt($now->modify('+1 hour'))
        ->withClaim('data', $claims)
        ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
        ->toString();

    $refreshToken = $this->jwtConfig->builder()
        ->issuedBy('http://localhost:8080') // TODO -> get string from config
        ->permittedFor('http://localhost:8080') // TODO -> get string from config
        ->issuedAt($now)
        ->expiresAt($now->modify('+1 week'))
        ->withClaim('data', $claims)
        ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
        ->toString();

    return [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
    ];
  }

  /**
   * Validates the JWT token from the request.
   */
  public function validate(Request $request) {
    $authHeader = $request->headers->get('Authorization');
    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
      \Drupal::logger('safetti_custom')->error('Authorization header missing or invalid.');
      return FALSE;
    }

    $token = substr($authHeader, 7);
    try {
      $jwtToken = $this->jwtConfig->parser()->parse($token);

      if (!$jwtToken instanceof Plain) {
        \Drupal::logger('safetti_custom')->error('Invalid token format.');
        return FALSE;
      }

      if (!$this->jwtConfig->validator()->validate($jwtToken, ...$this->jwtConfig->validationConstraints())) {
        \Drupal::logger('safetti_custom')->error('Token signature validation failed.');
        return FALSE;
      }
      
      $claims = $jwtToken->claims();
      if ($claims->get('exp')->getTimestamp() < time()) {
        \Drupal::logger('safetti_custom')->error('Token has expired.');
        return FALSE;
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('safetti_custom')->error('Error parsing token: @message', ['@message' => $e->getMessage()]);
      return FALSE;
    }

    return TRUE;
  }

  public function parseToken($tokenString) {
    return $this->jwtConfig->parser()->parse($tokenString);
  }
  
  public function isExpired($token) {
    return $token->claims()->get('exp')->getTimestamp() < time();
  }
  
  public function getClaims($token) {
    return $token->claims()->all();
  }
  
}
