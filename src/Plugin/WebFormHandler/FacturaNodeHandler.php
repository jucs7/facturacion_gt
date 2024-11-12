<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\node\Entity\Node;

class FacturaNodeHandler {

  public $factura;

  public function __construct(array $factura) {
    $this->factura = $factura;
  }
  
  public function createNode() {

    // Crear contenido de tipo factura electrónica
    $node = Node::create([
      'type' => 'factura_electronica',
      'title' => $this->factura['sequence'],
      'field_cliente' => [
        'value' => $this->factura['customer']['companyName'],
        'format' => 'plain_text',
      ],
      'field_fecha' => [
        'value' => $this->factura['date'],
        'format' => 'plain_text',
      ],
      'field_valor' => [
        'value' => $this->factura['totals']['amount'],
        'format' => 'plain_text',
      ],
      'field_pdf' => [
        'uri' => $this->factura['urlPdf'],
        'title' => 'PDF',
      ],
      'field_json' => [
        'value' => json_encode($this->factura),
        'format' => 'plain_text',
      ],
    ]);

    // Guardar contenido
    $node->save();
  }
}