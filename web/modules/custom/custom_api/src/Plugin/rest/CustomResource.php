<?php
// src/Plugin/rest/resource/customResource.php

namespace Drupal\custom_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserAuthInterface;
use Drupal\custom_api\Plugin\rest\resource\LoggerInterface;

/**
 * Provides a resource to authenticate users via token.
 *
 * @RestResource(
 *   id = "custom_resource",
 *   label = @Translation("Custom API Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/custom/auth"
 *   }
 * )
 */
class CustomResource extends ResourceBase {

    protected $currentUser;
    protected $userAuth;
    protected $entityTypeManager;
  
    public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      array $serializer_formats,
      LoggerInterface $logger,
      AccountProxyInterface $current_user,
      UserAuthInterface $user_auth,
      EntityTypeManagerInterface $entity_type_manager
    ) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
      $this->currentUser = $current_user;
      $this->userAuth = $user_auth;
      $this->entityTypeManager = $entity_type_manager;
    }
  
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->getParameter('serializer.formats'),
        $container->get('logger.factory')->get('your_module'),
        $container->get('current_user'),
        $container->get('user.auth'),
        $container->get('entity_type.manager')
      );
    }
  
    public function post(Request $request) {
      // Extract the token from the request.
      $token = $request->headers->get('Authorization');
  
      // Here you would validate the token against your authentication system.
      // This is an illustrative example. You'll need to implement the actual token validation.
      if ($token == "valid_token") {
        // Token is valid, return a positive response.
        return new ResourceResponse("Token valid", 200);
      } else {
        // Token is invalid.
        return new ResourceResponse("Invalid token", 403);
      }
    }
  }
