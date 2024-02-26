<?php
namespace Drupal\custom_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\file\Entity\File;
use \Drupal\taxonomy\Entity\Term;

class CustomApiController extends ControllerBase {
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  // Fetch specific content details.
  public function getContent(Request $request) {
    // Retrieve data from the request body.
    $data = json_decode($request->getContent(), TRUE);
    if (empty($data['nid'])) {
      return new JsonResponse(['error' => 'Node ID (nid) is required in the request body.'], 400);
    }

    // Load the node based on the provided nid.
    $node = Node::load($data['nid']);

    if (!$node) {
      // Handle the case where the node is not found.
      return new JsonResponse(['error' => 'Node not found'], 404);
    }

    // Get the desired fields or the entire node data.
    $nodeData = [
      'title' => $node->getTitle(),
      'body' => $node->get('body')->getValue(),
      // Add more fields as needed.
    ];

    // Return JSON response.
    return new JsonResponse($nodeData);
  }

  // Fetch specific content type content details.
  public function getContentTypeContent(Request $request) {
    // Retrieve data from the request body.
    $data = json_decode($request->getContent(), TRUE);
    
    if (empty($data['type'])) {
      return new JsonResponse(['error' => 'Content type is required in the request body.'], 400);
    }

    // Get article using entity query.
    $data_type = $data['type']; 

    // Get article using entity query
    $node_query = \Drupal::entityQuery('node')
            ->accessCheck(FALSE)
            ->condition('type', $data_type)
            ->execute();
     
    // get value of article results
    
    $nodes = Node::loadMultiple($node_query); 

    if (empty($nodes)) {
      // Handle the case where the node is not found.
      return new JsonResponse(['error' => 'Nodes are not found'], 404);
    }

    // Get the desired fields or the entire node data.
    foreach($nodes as $node_key => $node_value) {        
        $node_data[] = [
        "nid" => $node_value->nid->value,
        "title" => $node_value->title->value,
        "body" => $node_value->body->value,
        "tags_id" => $node_value->field_tags->target_id,
        "file_id" => $node_value->field_image->target_id];
    }

    // Return JSON response.
    return new JsonResponse($node_data);
  }

  /**
  * Callback function to create a node.
  */
  function app_submit(Request $request) {
    // Get JSON data from the request body.
    $data = json_decode($request->getContent(), TRUE);
    
    if (empty($data['title'])) {
      return new JsonResponse(['error' => 'title is required in the request body to create the node.'], 400);
    }

    // Create a new file object
    $file = File::create([
      'uri' => 'public://'.$data['image'],
      'status' => 1,
    ]);
    $file->save();

    $term = Term::create([
      'name' => $data['tags'],
      'vid' => 'front_apps',
    ]);
    $term->save();

    echo "<pre>";
    print_r($file);
    print_r($term);
    die('----d-----');

    // Create a node entity.
    $node = Node::create([
      'type' => 'article', // create a node of 'article' content type.
      'title' => $data['title'],
      'body' => $data['body'],
      'uid' => $data['uid'],

      // Add more fields as needed.
    ]);

    // add file object to the node object
    $node->set('field_media_image', [
      'target_id' => $file->id(),
    ]);

    $node->save();

    // Return the ID of the created node.
    return new Response($node->id(), Response::HTTP_CREATED);


  }

}