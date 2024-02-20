<?php
namespace Drupal\custom_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
}
