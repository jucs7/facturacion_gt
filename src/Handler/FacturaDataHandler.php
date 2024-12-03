<?php

namespace Drupal\facturacion_gt\Handler;

use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

class FacturaDataHandler {

  public function prepareFacturaData(array $values, array $factura) {
    // Inicializar precio total
    $amount = 0;

    // Fecha
    $factura['date'] = date('Y-m-d\TH:i:s');

    // Cargar datos del cliente
    $customer = User::load($values['cliente']);

    // Obtener tipo de persona
    $tipo_de_persona = $customer->get('field_tipo_de_persona')->value;

    // Ingresar valores en la factura
    if ($tipo_de_persona == '2') {
      // Si es Persona Natural
      $factura['customer']['companyName'] = $customer->get('field_nombre_completo')->value;
      $factura['customer']['personType'] = $tipo_de_persona;
      $factura['customer']['firstName'] = $customer->get('field_nombres')->value;
      $factura['customer']['lastName'] = $customer->get('field_apellidos')->value;
      $factura['customer']['identification'] = $customer->get('field_identificacion')->value;
    }
    elseif ($tipo_de_persona == '1') {
      // Si es Persona Jurídica
      $factura['customer']['companyName'] = $customer->get('field_nombre_completo')->value;
      $factura['customer']['personType'] = $tipo_de_persona;
      $factura['customer']['identification'] = $customer->get('field_identificacion')->value;
      $factura['customer']['digitCheck'] = $customer->get('field_digito_de_verificacion')->value;
      // Limpiar los campos de Persona Natural.
      $factura['customer']['firstName'] = "";
      $factura['customer']['lastName'] = "";
    }
    
    $factura['customer']['identificationTypeCode'] = $customer->get('field_tipo_de_identificacion')->value;
    $factura['customer']['email'] = $customer->get('field_email')->value;
    $factura['customer']['phone'] = $customer->get('field_telefono')->value;
    $factura['customer']['responsibilities'] = $customer->get('field_responsabilidades_fiscales')->value;
    $factura['customer']['regimeType'] = $customer->get('field_tipo_de_regimen')->value;

    // Obtener productos
    $productos = $values['productos_composite'];
    $invoice_details = $factura['invoiceDetails'];
    $invoice_details = array_slice($invoice_details, 0, 1);
    
    // Ingresar valores de productos
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

      $invoice_details[$i]['itemCode'] = $item->get('field_codigo')->value;
      $invoice_details[$i]['itemName'] = $item->get('field_nombre')->value;
      $invoice_details[$i]['price'] = $item->get('field_precio')->value;
      $invoice_details[$i]['quantity'] = $productos[$i]['cantidad'];

      // Sumar al total
      $amount += floatval($item->get('field_precio')->value) * floatval($productos[$i]['cantidad']);

      // Verificar si hay más productos y agregarlo
      if ($i != count($productos) - 1) {
        $invoice_details[] = $invoice_details[$i];
      }
    }

    $factura['paymentMeanCode'] = $values['forma_de_pago'];
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
