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
    $totalsAmount = $invoiceData['totals']['amount'];
    $sequence = $invoiceData['sequence'];
    $date = $invoiceData['date'];
    $dateDue = $invoiceData['dateDue'];
    $cufe = $invoiceData['cufe'];

    return "Total: $totalsAmount\n" .
      "Consecutivo: $sequence\n" . 
      "Fecha: $date\n" . 
      "Fecha de vencimiento: $dateDue\n" .
      "Cufe: $cufe";
  }

  public function customerDataHandler(array $customerData) {
    $companyName = $customerData['companyName'];
    $identification = $customerData['identification'];
    $email = $customerData['email'];
    $phone = $customerData['phone'];

    if ($customerData['personType'] == 1) {
      // Si es persona jurídica
      $identification .= "-" . $customerData['digitCheck'];
    }
    
    return "Nombre: $companyName\n" .
      "Identificación: $identification\n" .
      "Email: $email\n" .
      "Teléfono: $phone";
  }

  public function detailDataHandler(array $detailData) {
    $invoiceDetails = "";

    foreach ($detailData as $item) {
      $itemCode = $item['itemCode'];
      $itemName = $item['itemName'];
      $price = $item['price'];
      $quantity = $item['quantity'];
      $subTotal = $item['subTotal'];

      $invoiceDetails .= "Código de ítem: $itemCode\n" .
        "Nombre de ítem: $itemName\n" .
        "Precio: $price\n" .
        "Cantidad: $quantity\n" .
        "Subtotal: $subTotal\n\n";
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
    ]);

    // Guardar contenido
    $node->save();
  }
}