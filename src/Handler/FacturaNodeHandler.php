<?php

namespace Drupal\facturacion_gt\Handler;

use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\facturacion_gt\Handler\ServicioNodeHandler;

class FacturaNodeHandler {

  public $factura;

  public function __construct(array $factura) {
    $this->factura = $factura;
  }
  
  public function createFacturaNode() {

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
      'field_estado' => [
        'value' => true
      ]
    ]);

    // Guardar contenido
    $node->save();
  }

  public function createFacturaNoElecNode() {
    // Cargar datos del cliente
    $customer = User::load($this->factura['cliente']);
    $personType = '';
    $identificationType = '';

    if ($customer->get('field_tipo_de_persona')->value == '1') {
      $personType = 'Persona juridica';
    } else {
      $personType = 'Persona natural';
    }

    if ($customer->get('field_tipo_de_identificacion')->value == '11') {
      $identificationType = 'Registro civil';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '12') {
      $identificationType = 'Tarjeta de identidad';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '13') {
      $identificationType = 'Cédula de ciudadanía';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '21') {
      $identificationType = 'Tarjeta de extranjería';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '22') {
      $identificationType = 'Cédula de extranjería.';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '31') {
      $identificationType = 'NIT';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '41') {
      $identificationType = 'Pasaporte';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '42') {
      $identificationType = 'Documento de identificación extranjero ';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '50') {
      $identificationType = 'NIT de otro país ';
    } else if ($customer->get('field_tipo_de_identificacion')->value == '91') {
      $identificationType = 'NUIP';
    }

    // Detalle de productos y servicios 
    $productos = $this->factura['productos_composite'];
    $servicios = $this->factura['servicios_composite'];
    $detalle = '';
    $total = 0;

    for ($i = 0; $i < count($productos); $i++) {
      // Cargar datos del producto
      $item = Node::load($productos[$i]['producto']);

      // Verificar si hay suficientes unidades en existencia
      if ($item->get('field_stock')->value < $productos[$i]['cantidad']) {
        \Drupal::messenger()->addError('
          No hay suficientes unidades del producto: ' . $item->get('field_nombre')->value . 
          ', Unidades en existencia: ' . $item->get('field_stock')->value
        );
        return false;
      }

      $itemCode = $item->get('field_codigo')->value;
      $itemName = $item->get('field_nombre')->value;
      $price = $item->get('field_precio')->value;
      $quantity = $productos[$i]['cantidad'];
      $subtotal = $price * $quantity;

      $detalle .= "Codigo: " . $itemCode . " | " .
        "Producto: " . $itemName . " | " .
        "Precio: " . $price . " | " .
        "Cantidad: " . $quantity . " | " .
        "Subtotal: " . $subtotal . "<br>"; 

      $total += $subtotal;
    }

    for ($i =0; $i < count($servicios); $i++) {
      // Cargar datos del producto alquilado
      $servicio = Node::load($servicios[$i]['servicio']);

      $serviceName = $servicio->get('field_nombre')->value;
      $diasAlquiler = $servicios[$i]['horas'];
      $subtotal = $servicios[$i]['subtotal'];

      $detalle .= $serviceName . " | " . $diasAlquiler . " dias | Subtotal: " . $subtotal . "<br>";

      $total += $subtotal;
    }

    // Crear contenido de tipo factura no electrónica
    $node = Node::create([
      'type' => 'factura_no_electronica',
      'title' => $this->factura['numero_de_factura'],
      'field_numero_de_factura' => [
        'value' => $this->factura['numero_de_factura'],
        'format' => 'plain_text',
      ],
      'field_fecha' => [
        'value' => date('Y-m-d\TH:i:s'),
        'format' => 'plain_text',
      ],
      'field_tipo_de_persona' => [
        'value' => $personType,
        'format' => 'plain_text',
      ],
      'field_tipo_de_identificacion' => [
        'value' => $identificationType,
        'format' => 'plain_text',
      ],
      'field_identificacion' => [
        'value' => $customer->get('field_identificacion')->value,
        'format' => 'plain_text',
      ],
      'field_nombre_cliente' => [
        'value' => $customer->get('field_nombre_completo')->value,
        'format' => 'plain_text',
      ],
      'field_email' => [
        'value' => $customer->get('field_email')->value,
        'format' => 'email',
      ],
      'field_telefono' => [
        'value' => $customer->get('field_telefono')->value,
        'format' => 'plain_text',
      ],
      'field_detalle' => [
        'value' => $detalle,
        'format' => 'basic_html',
      ],
      'field_valor' => [
        'value' => $total,
        'format' => 'plain_text',
      ],
    ]);

    // Guardar contenido
    $node->save();

    // Reducir stock de los productos
    $stockHandler = new StockHandler();
    $stockHandler->reducirStock($productos);
  }
}