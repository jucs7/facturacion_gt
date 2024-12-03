<?php

namespace Drupal\facturacion_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class ProductDataController extends ControllerBase{
  public function getProductData($pid) {
    $product = Node::load($pid);
    if ($product) {
      return new JsonResponse([
        'producto' => $product->getTitle(),
        'field_categoria' => $product->get('field_categoria')->entity->getName(),
        'field_codigo' => $product->get('field_codigo')->value,
        'field_nombre' => $product->get('field_nombre')->value,
        'field_precio' => $product->get('field_precio')->value,
        'field_proveedor' => $product->get('field_proveedor')->entity->getDisplayName(),
        'field_stock' => $product->get('field_stock')->value,
        'field_stock_de_emergencia' => $product->get('field_stock_de_emergencia')->value,
      ]);
    }

    return new JsonResponse([], 404);
  }
}
