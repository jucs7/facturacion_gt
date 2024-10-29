<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\facturacion_gt\Plugin\WebformHandler\FacturaDataHandler;

/**
 * Webform submission handler for Facturacion GT.
 *
 * @WebformHandler(
 *   id = "facturacion_gt_webform_handler",
 *   label = @Translation("Facturación GT Webform Handler"),
 *   category = @Translation("Custom"),
 *   description = @Translation("Updates the factura.json file based on Webform submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class FacturacionGtWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
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

    // Obtener valores de la configuración.
    $config = \Drupal::configFactory()->getEditable('facturacion_gt.settings');

    // Consecutivo y Resolución
    $consecutive = $config->get('consecutive');
    $resolution_init_range = $config->get('resolutionRangeInitial');
    $resolution_final_range = $config->get('resolutionRangeFinal');

    // Verificar si el consecutivo está dentro del rango.
    if ($consecutive > $resolution_final_range && $consecutive < $resolution_init_range) {
      \Drupal::messenger()->addError('El rango de resolución ha sido alcanzado.');
      return;
    }

    // Obtener los valores enviados en el Webform.
    $values = $webform_submission->getData();

    // Utilizar FacturaDataHandler para ingresar valores en la factura.
    $facturaHandler = new FacturaDataHandler();
    $factura_data = $facturaHandler->prepareFacturaData($values, $factura_data);

    // Fecha
    $date = date('Y-m-d');
    $factura_data['date'] = $date;
    $factura_data['dateDue'] = $date;
    $factura_data['dateStart'] = $date;
    $factura_data['dateEnd'] = $date;

    // Datos de resolución
    $factura_data['consecutive'] = $consecutive;
    $factura_data['resolution']['resolutionRangeInitial'] = $resolution_init_range;
    $factura_data['resolution']['resolutionRangeFinal'] = $resolution_final_range;

    // Guardar los nuevos datos en el archivo JSON.
    file_put_contents($json_file, json_encode($factura_data, JSON_PRETTY_PRINT));

    // Enviar los datos actualizados a la API.
    $apiClient = new ApiClient();
    $response = $apiClient->enviarFactura($factura_data);

    // Manejar la respuesta de la API
    if ($response['success']) {
      // Incrementar el consecutivo en la configuración solo si el envío es exitoso.
      $config->set('consecutive', $consecutive + 1)->save();
      \Drupal::messenger()->addMessage($response['message']);
    } else {
      \Drupal::messenger()->addError($response['message']);
    }
  }

}
