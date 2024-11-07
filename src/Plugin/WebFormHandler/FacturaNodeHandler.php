<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\node\Entity\Node;

class FacturaNodeHandler {

  protected $factura;
  protected $invoice;
  protected $customer;
  protected $detail;
  protected $urlPdf;
  
  public function __construct(array $factura) {
    $this->factura = $factura;
    $this->invoice = $this->invoiceDataHandler($factura);
    $this->customer = $this->customerDataHandler($factura['customer']);
    $this->detail = $this->detailDataHandler($factura['invoiceDetails']);
    $this->urlPdf = $factura['urlPdf'];
  }

  public function invoiceDataHandler(array $invoiceData) {
    return "Total: {$invoiceData['totals']['amount']}\n" .
      "Consecutivo: {$invoiceData['sequence']}\n" . 
      "Fecha: {$invoiceData['date']}\n" . 
      "Fecha de vencimiento: {$invoiceData['dateDue']}\n" .
      "Cufe: {$invoiceData['cufe']}";
  }

  public function customerDataHandler(array $customerData) {
    $identification = $customerData['identification'];

    if ($customerData['personType'] == 1) {
      // Si es persona jurídica agrega el dígito de verificación
      $identification .= "-" . $customerData['digitCheck'];
    }
    
    return "Nombre: {$customerData['companyName']}\n" .
      "Identificación: $identification\n" .
      "Email: {$customerData['email']}\n" .
      "Teléfono: {$customerData['phone']}";
  }

  public function detailDataHandler(array $detailData) {
    $invoiceDetails = "";

    foreach ($detailData as $item) {
      $invoiceDetails .= "Código de ítem: {$item['itemCode']}\n" .
        "Nombre de ítem: {$item['itemName']}\n" .
        "Precio: {$item['price']}\n" .
        "Cantidad: {$item['quantity']}\n" .
        "Subtotal: {$item['subTotal']}\n\n";
    }
    
    return $invoiceDetails;
  }
  
  public function createNode() {

    // Crear contenido de tipo factura electrónica
    $node = Node::create([
      'type' => 'factura_electronica',
      'title' => 'Factura electrónica de venta ' . $this->factura['sequence'],
      'field_factura' => [
        'value' => $this->invoice,
        'format' => 'plain_text',
      ],
      'field_pdf' => [
        'value' => "<a href=\"$this->urlPdf\" target=\"_blank\">PDF</a>",
        'format' => 'full_html',
      ],
      'field_cliente' => [
        'value' => $this->customer,
        'format' => 'plain_text',
      ],
      'field_detalle' => [
        'value' => $this->detail,
        'format' => 'plain_text',
      ],
      'field_factura_json' => [
        'value' => json_encode($this->factura),
        'format' => 'plain_text',
      ],
    ]);

    // Guardar contenido
    $node->save();
  }
}