<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    // Obtener los valores enviados en el Webform.
    $values = $webform_submission->getData();

    // Comprobar si es Persona Natural o Jurídica.
    if (!empty($values['persona_natural'])) {
      // Si es Persona Natural
      $factura_data['customer']['companyName'] = $values['nombres'] . ' ' . $values['apellidos'];
      $factura_data['customer']['personType'] = '2';
      $factura_data['customer']['firstName'] = $values['nombres'];
      $factura_data['customer']['lastName'] = $values['apellidos'];
      $factura_data['customer']['identification'] = $values['identificacion'];
      $factura_data['customer']['identificationTypeCode'] = '13';
    }
    elseif (!empty($values['persona_juridica'])) {
      // Si es Persona Jurídica
      $factura_data['customer']['companyName'] = $values['razon_social'];
      $factura_data['customer']['personType'] = '1';
      $factura_data['customer']['identification'] = $values['nit'];
      $factura_data['customer']['identificationTypeCode'] = '31';
      // Limpiar los campos de Persona Natural.
      $factura_data['customer']['firstName'] = "";
      $factura_data['customer']['lastName'] = "";
    }

    // Guardar los nuevos datos en el archivo JSON.
    file_put_contents($json_file, json_encode($factura_data, JSON_PRETTY_PRINT));
    
    // Enviar la factura a la API
    $this->enviarFactura($factura_data);

    // Mensaje de éxito.
    // \Drupal::messenger()->addMessage('La factura se ha actualizado correctamente.');

  }

  /**
   * Envía los datos de la factura a la API.
   *
   * @param array $factura_data
   *   Datos de la factura a enviar.
   */
  private function enviarFactura(array $factura_data) {
    // Obtener el servicio HTTP Client.
    $client = new Client();

    // URL de la API.
    $api_url = 'https://isv.aliaddo.net/api/v1/public/documents/invoice/test';

    try {
      // Realizar request por metodo POST
      $response = $client->post($api_url, [
        'json' => $factura_data,
        // Adicionar cabeceras de autenticación con token bearer
        'headers' => [
          'x-api-key' => 'key-3440faa4a3b2419aa1c3ac3b2a457b8d-031610',
          'content-type' => 'application/json',
          'cache-control' => 'no-cache',
          'accept' => 'application/json',
        ],
      ]);

      // Decodificar la respuesta de la API
      $body = json_decode($response->getBody(), TRUE);

      // Mostrar respuesta o error si hubo alguno
      if ($response->getStatusCode() == 200) {
        return new JsonResponse($body);

        \Drupal::messenger()->addMessage('La factura se ha enviado correctamente.');
      }
      else {
        return new JsonResponse([
          'status' => 'error',
          'message' => 'Error al enviar la factura',
          'response' => $body,
        ]);

        \Drupal::messenger()->addError('Se ha producido un error al enviar la factura.');
      }
    }
    catch (\Exception $e) {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Excepción: ' . $e->getMessage(),
      ]);
    }
  }
}
