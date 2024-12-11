<?php

namespace Drupal\facturacion_gt\Handler;

use DateTime;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

class ServicioNodeHandler {

  public function createAlquilerNode(Node $producto, User $customer, $dias, $precio) {
    $fecha_inicial = date('Y-m-d\TH:i:s');
    $fecha = new DateTime($fecha_inicial);
    $fecha->modify('+' . $dias . ' days');
    $fecha_fin = $fecha->format('Y-m-d\TH:i:s');

    $node = Node::create([
      'type' => 'servicios',
      'title' => 'Alquiler ' . $producto->get('field_nombre')->value,
      'field_categoria_servicio' => [
        'target_id' => '2',
      ],
      'field_cliente_servicio' => [
        'target_id' => $customer->id(),
      ],
      'field_producto' => [
        'target_id' => $producto->id(),
      ],
      'field_dias_de_alquiler' => [
        'value' => $dias,
        'format' => 'number',
      ],
      'field_precio' => [
        'value' => $precio,
        'format' => 'number',
      ],
      'field_inicio_alquiler' => [
        'value' => $fecha_inicial,
        'format' => 'plain_text',
      ],
      'field_fin_alquiler' => [
        'value' => $fecha_fin,
        'format' => 'plain_text',
      ],
    ]);

    $node->save();
  }
}
