<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient {

  protected $client;
  protected $apiUrl;
  protected $apiKey;

  public function __construct() {
    $config = \Drupal::configFactory()->getEditable('facturacion_gt.invoice_settings');
    $this->client = new Client();
    $this->apiUrl = $config->get('endpoint');
    $this->apiKey = $config->get('apiKey');
  }

  public function enviarFactura(array $facturaData) {
    try {
      $response = $this->client->post($this->apiUrl, [
        'json' => $facturaData,
        // Adicionar cabeceras de autenticación con token Bearer
        'headers' => [
          'x-api-key' => $this->apiKey,
          'content-type' => 'application/json',
          'cache-control' => 'no-cache',
          'accept' => 'application/json',
        ],
      ]);

      return $this->handleResponse($response);
    } catch (RequestException $e) {
      // Manejo de excepciones
      \Drupal::logger('facturacion_gt')->error('Ocurrió un error al intentar enviar la factura: @message', ['@message' => $e->getMessage()]);
      return [
        'success' => false,
        'message' => 'Ocurrió un error al intentar enviar la factura: ' . $e->getMessage(),
      ];
    }
  }

  protected function handleResponse($response) {
    // Decodificar la respuesta de la API
    $body = json_decode($response->getBody(), TRUE);

    // Verificar el estado de la respuesta
    if ($response->getStatusCode() == 200) {
      // Factura enviada correctamente a la API
      if ($body["dianState"] == 00) {
        // Si no hubo códigos de rechazo de la DIAN
        \Drupal::logger('facturacion_gt')->info('La factura ha sido enviada correctamente. @response', ['@response' => json_encode($body)]);
        return [
          'success' => true,
          'message' => 'La factura ha sido enviada correctamente.',
          'data' => $body,
        ];
      } else {
        // Si hubo códigos de rechazo de la DIAN
        \Drupal::logger('facturacion_gt')->error('Error al enviar la factura. @status', ['@response' => json_encode($body["dianStateReason"])]);
        return [
          'success' => false,
          'message' => 'Error al enviar la factura.' . json_encode($body["dianStateReason"]),
        ];
      }

    } else {
      \Drupal::logger('facturacion_gt')->error('Error al enviar la factura. @status', ['@status' => $response->getStatusCode()]);
      return [
        'success' => false,
        'message' => 'Error al enviar la factura.',
      ];
    }
  }

}
