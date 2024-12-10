<?php

namespace Drupal\facturacion_gt\Handler;

use Drupal\node\Entity\Node;
use Drupal\facturacion_gt\Handler\StockAlertHandler;

class StockHandler {

  public function ingresarStock(array $productos) {

    for ($i = 0; $i < count($productos); $i++) {
      $producto = Node::load($productos[$i]['producto']);

      // Obtener valor del stock actual e incrementarlo
      $stock_actual = $producto->get('field_stock')->value;
      $stock_inc = $stock_actual + $productos[$i]['cantidad'];

      $producto->set('field_stock', $stock_inc);

      $producto->save();

      $alertHandler = new StockAlertHandler($producto);
      $alertHandler->deleteAlerts();

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
        $alerta = Node::create([
          'type' => 'alerta_de_stock',
          'title' => 'Alerta de stock ' . $producto->get('field_nombre')->value,
          'field_producto' => $producto,
          'field_tipo_de_alerta' => [
            'value' => 'Unidades agotadas',
            'format' => 'plain_text'
          ],
          'field_unidades_disponibles' => [
            'value' => $producto->get('field_stock')->value,
            'format' => 'number'
          ],
          'body' => [
            'value' => 'Unidades disponibles del producto ' . $producto->get('field_nombre')->value . 
              ': ' . $producto->get('field_stock')->value,
            'format' => 'plain_text'
          ],
        ]);

        $alerta->save();

        \Drupal::logger('facturacion_gt')->notice('ALERTA DE STOCK, @PRODUCTO AGOTADO', [
          '@PRODUCTO' => $producto->get('field_nombre')->value
        ]);
      }
      else if ($producto->get('field_stock')->value <= $producto->get('field_stock_de_emergencia')->value){
        $alerta = Node::create([
          'type' => 'alerta_de_stock',
          'title' => 'Alerta de stock ' . $producto->get('field_nombre')->value,
          'field_producto' => $producto,
          'field_tipo_de_alerta' => [
            'value' => 'Pocas unidades',
            'format' => 'plain_text'
          ],
          'field_unidades_disponibles' => [
            'value' => $producto->get('field_stock')->value,
            'format' => 'number'
          ],
          'body' => [
            'value' => 'Unidades disponibles del producto ' . $producto->get('field_nombre')->value . 
              ': ' . $producto->get('field_stock')->value,
            'format' => 'plain_text'
          ],
        ]);

        $alerta->save();

        \Drupal::logger('facturacion_gt')->notice('ALERTA DE POCAS UNIDADES DE STOCK, @PRODUCTO UNIDADES: @EXISTENCIAS', [
          '@PRODUCTO' => $producto->get('field_nombre')->value,
          '@EXISTENCIAS' => $producto->get('field_stock')->value
        ]);
      }

    }
  }

}