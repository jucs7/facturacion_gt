<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\facturacion_gt\Handler\FacturaDataHandler;
use Drupal\facturacion_gt\Handler\FacturaNodeHandler;

/**
 * Webform submission handler for Facturacion GT.
 *
 * @WebformHandler(
 *   id = "facturar_webform_handler",
 *   label = "Facturar Webform Handler",
 *   category = "Custom",
 *   description = "Envía facturas a la API de Aliaddo.",
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class FacturarWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    // Config
    $config = \Drupal::configFactory()->getEditable('facturacion_gt.invoice_settings');

    // Ruta al archivo JSON.
    $module_path = \Drupal::service('extension.path.resolver')->getPath('module', 'facturacion_gt');
    $json_file = $module_path . '/config/factura.json';

    // Verificar si el archivo JSON existe.
    if (!file_exists($json_file)) {
      \Drupal::messenger()->addError('El archivo JSON no se encontró.');
      return;
    }

    // Leer el contenido actual del archivo JSON.
    $factura_data = json_decode(file_get_contents($json_file), TRUE);

    // Obtener los valores enviados en el Webform.
    $values = $webform_submission->getData();

    // Utilizar FacturaDataHandler para ingresar valores en la factura.
    $facturaHandler = new FacturaDataHandler();
    $factura_data = $facturaHandler->prepareFacturaData($values, $factura_data);

    // Guardar los nuevos datos en el archivo JSON.
    file_put_contents($json_file, json_encode($factura_data, JSON_PRETTY_PRINT));
    
    // Verificar si el consecutivo está dentro del rango.
    if ($factura_data['consecutive'] > $factura_data['resolution']['resolutionRangeFinal'] && $factura_data['consecutive'] < $factura_data['resolution']['resolutionRangeInitial']) {
      \Drupal::messenger()->addError('El rango de facturación ha sido alcanzado.');
      return;
    }

    // Enviar los datos actualizados a la API.
    $apiClient = new ApiClient();
    $response = $apiClient->enviarFactura($factura_data);

    // Manejar la respuesta de la API
    if ($response['success']) {
      // Incrementar el consecutivo en la configuración solo si el envío es exitoso.
      $config->set('consecutive', $factura_data['consecutive'] + 1)->save();

      // Crear contenido de tipo factura
      $nodeHandler = new FacturaNodeHandler($response['data']);
      $nodeHandler->createNode();

      \Drupal::messenger()->addMessage($response['message']);
    } else {
      \Drupal::messenger()->addError($response['message']);
    }
  }

}
