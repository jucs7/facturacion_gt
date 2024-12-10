<?php

namespace Drupal\facturacion_gt\Handler;

use Drupal\node\Entity\Node;

class StockAlertHandler {

  protected $producto;

  public function __construct(Node $producto) {
    $this->producto = $producto;
  }

  public function deleteAlerts() {

    $stock = $this->producto->get('field_stock')->value;
    $stockDeEmergencia = $this->producto->get('field_stock_de_emergencia')->value;

    if ($stock > $stockDeEmergencia) {
      $entity_query = \Drupal::entityQuery('node')
        ->condition('type', 'alerta_de_stock') 
        ->condition('field_producto.target_id', $this->producto->id())
        ->accessCheck(TRUE); 

      $nids = $entity_query->execute(); 
      $alert_nodes = Node::loadMultiple($nids);

      foreach ($alert_nodes as $alert_node) {
        $alert_node->delete();
      }
    }

  }


}