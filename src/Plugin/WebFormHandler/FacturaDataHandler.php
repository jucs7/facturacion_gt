<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

class FacturaDataHandler {

  public function prepareFacturaData(array $values, array $factura) {
    // Inicializar precio total
    $amount = 0;

    // Fecha
    $factura['date'] = date('Y-m-d\TH:i:s');

    // Ingresar valores en la factura
    if (!empty($values['persona_natural'])) {
      // Si es Persona Natural
      $factura['customer']['companyName'] = $values['nombres'] . ' ' . $values['apellidos'];
      $factura['customer']['personType'] = '2';
      $factura['customer']['firstName'] = $values['nombres'];
      $factura['customer']['lastName'] = $values['apellidos'];
      $factura['customer']['identification'] = $values['identificacion'];
      $factura['customer']['identificationTypeCode'] = '13';
    }
    elseif (!empty($values['persona_juridica'])) {
      // Si es Persona Jurídica
      $factura['customer']['companyName'] = $values['razon_social'];
      $factura['customer']['personType'] = '1';

      // Obtener NIT y dígito de verificación
      $factura['customer']['identification'] = substr($values['nit'], 0, -1);;
      $factura['customer']['digitCheck'] = substr($values['nit'], -1);
      $factura['customer']['identificationTypeCode'] = '31';
      // Limpiar los campos de Persona Natural.
      $factura['customer']['firstName'] = "";
      $factura['customer']['lastName'] = "";
    }

    $factura['customer']['email'] = $values['email'];
    $factura['customer']['phone'] = $values['telefono'];

    // Obtener productos
    $productos = $values['productos_composite'];
    $invoice_details = $factura['invoiceDetails'];
    $invoice_details = array_slice($invoice_details, 0, 1);
    
    // Ingresar valores de productos
    for ($i = 0; $i < count($productos); $i++) {
      $invoice_details[$i]['itemCode'] = $productos[$i]['codigo_item'];
      $invoice_details[$i]['itemName'] = $productos[$i]['nombre_item'];
      $invoice_details[$i]['price'] = $productos[$i]['precio_item'];
      $invoice_details[$i]['quantity'] = $productos[$i]['cantidad_item'];

      // Sumar al total
      $amount += floatval($productos[$i]['precio_item']) * floatval($productos[$i]['cantidad_item']);

      // Verificar si hay más productos y agregarlo
      if ($i != count($productos) - 1) {
        $invoice_details[] = $invoice_details[$i];
      }
    }

    $factura['invoiceDetails'] = $invoice_details;
    $factura['totals']['amount'] = $amount;

    // Obtener valores de la configuración.
    $config = \Drupal::configFactory()->getEditable('facturacion_gt.invoice_settings');

    // Consecutivo
    $factura['consecutive'] = $config->get('consecutive');

    // Datos de resolución
    $factura['resolution']['resolutionKey'] = $config->get('resolutionKey');
    $factura['resolution']['resolutionPrefix'] = $config->get('resolutionPrefix');
    $factura['resolution']['resolutionNumber'] = $config->get('resolutionNumber');
    $factura['resolution']['resolutionRangeInitial'] = $config->get('resolutionRangeInitial');
    $factura['resolution']['resolutionRangeFinal'] = $config->get('resolutionRangeFinal');
    $factura['resolution']['resolutionValidFrom'] = $config->get('resolutionValidFrom');
    $factura['resolution']['resolutionValidUntil'] = $config->get('resolutionValidUntil');

    return $factura;
  }

}
