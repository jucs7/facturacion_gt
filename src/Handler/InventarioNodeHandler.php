<?php

namespace Drupal\facturacion_gt\Handler;

use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class InventarioNodeHandler {
  public function createNode(array $productos) {
    $paragraphs = [];
    for ($i = 0; $i < count($productos); $i++) {
      $producto = Node::load($productos[$i]['producto']);
      $paragraph = Paragraph::create([
        'type' => 'inventario',
        'field_producto' => $producto->id(),
        'field_stock_previo' => $productos[$i]['stock'],
        'field_stock_actualizado' => $productos[$i]['stock_actualizado'],
      ]);

      $paragraph->save();

      $paragraphs[] = ['target_id' => $paragraph->id(), 'target_revision_id' => $paragraph->getRevisionId()];
    }

    $node = Node::create([
      'type' => 'inventario',
      'title' => 'Inventario',
      'field_fecha_inventario' => date('Y-m-d'),
      'field_producto_inventario' => $paragraphs,
    ]);

    $node->save();
  }
}
