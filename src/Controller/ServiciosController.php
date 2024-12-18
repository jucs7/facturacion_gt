<?php

namespace Drupal\facturacion_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class ServiciosController extends ControllerBase{
  public function getServicio($sid) {
    $servicio = Node::load($sid);
    if ($servicio) {
      return new JsonResponse([
        'servicio' => $servicio->getTitle() ?? '',
        'field_categoria_servicio' => $servicio->get('field_categoria_servicio')->entity->getName() ?? '',
        'field_nombre' => $servicio->get('field_nombre')->value ?? '',
        'field_precio' => $servicio->get('field_precio')->value ?? '',
      ]);
    }

    return new JsonResponse([], 404);
  }
}
