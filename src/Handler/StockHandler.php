<?php

namespace Drupal\facturacion_gt\Handler;

use Drupal\node\Entity\Node;

class StockHandler {

  public function ingresarStock(array $productos) {

    for ($i = 0; $i < count($productos); $i++) {
      // Cargar nodo del producto
      $producto = Node::load($productos[$i]['producto']);

      // Obtener valor del stock actual e incrementarlo
      $stock_actual = $producto->get('field_stock')->value;
      $stock_inc = $stock_actual + $productos[$i]['cantidad'];

      $producto->set('field_stock', $stock_inc);

      $producto->save();

      \Drupal::logger('facturacion_gt')->notice('INGRESO DE STOCK, @PRODUCTO/@CANTIDAD/@EXISTENCIAS', [
        '@PRODUCTO' => $producto->get('field_nombre')->value,
        '@CANTIDAD' => $productos[$i]['cantidad'],
        '@EXISTENCIAS' => $producto->get('field_stock')->value,
      ]);

    }

	}

  public function reducirStock(array $productos) {
    for ($i = 0; $i < count($productos); $i++) {
      // Cargar nodo del producto
      $producto = Node::load($productos[$i]['producto']);

      // Obtener valor del stock actual y reducirlo
      $stock_actual = $producto->get('field_stock')->value;
      $stock_inc = $stock_actual - $productos[$i]['cantidad'];

      $producto->set('field_stock', $stock_inc);

      $producto->save();

      \Drupal::logger('facturacion_gt')->notice('REDUCCION DE STOCK, @PRODUCTO/@CANTIDAD/@EXISTENCIAS', [
        '@PRODUCTO' => $producto->get('field_nombre')->value,
        '@CANTIDAD' => $productos[$i]['cantidad'],
        '@EXISTENCIAS' => $producto->get('field_stock')->value,
      ]);

      // Alerta de stock
      if ($producto->get('field_stock')->value === 0) {
        \Drupal::logger('facturacion_gt')->notice('ALERTA DE STOCK, @PRODUCTO AGOTADO', [
          '@PRODUCTO' => $producto->get('field_nombre')->value
        ]);
      }
      else if ($producto->get('field_stock')->value <= $producto->get('field_stock_de_emergencia')->value){
        \Drupal::logger('facturacion_gt')->notice('ALERTA DE POCAS UNIDADES DE STOCK, @PRODUCTO UNIDADES: @EXISTENCIAS', [
          '@PRODUCTO' => $producto->get('field_nombre')->value,
          '@EXISTENCIAS' => $producto->get('field_stock')->value
        ]);
      }

    }
  }

}