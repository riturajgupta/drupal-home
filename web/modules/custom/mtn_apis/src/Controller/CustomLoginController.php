<?php

namespace Drupal\mtn_apis\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomLoginController extends ControllerBase {

  public function login(Request $request) {
    $content = $request->getContent();
    $credentials = json_decode($content, TRUE);

    $username = $credentials['username'] ?? '';
    $password = $credentials['password'] ?? '';

    // Authenticate user
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $user_auth = \Drupal::service('user.auth');
    $uid = $user_auth->authenticate($username, $password);

    if ($uid) {
      $user = $user_storage->load($uid);
      // Perform operations after successful login
      // ...

      return new JsonResponse(['message' => 'Login successful', 'user' => $user->toArray()]);
    } else {
      return new JsonResponse(['message' => 'Invalid credentials'], 401);
    }
  }
}
