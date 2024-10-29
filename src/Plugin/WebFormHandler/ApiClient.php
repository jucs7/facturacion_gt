<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient {

  protected $client;
  protected $apiUrl;
  protected $apiKey;

  public function __construct() {
    $this->client = new Client();
    $this->apiUrl = 'https://isv.aliaddo.net/api/v1/public/documents/invoice/test';
    $this->apiKey = 'key-3440faa4a3b2419aa1c3ac3b2a457b8d-031610';
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
      \Drupal::logger('facturacion_gt')->info('La factura ha sido enviada correctamente. @response', ['@response' => json_encode($body)]);
      return [
        'success' => true,
        'message' => 'La factura ha sido enviada correctamente.',
        'data' => $body,
      ];
    } else {
      \Drupal::logger('facturacion_gt')->error('Error al enviar la factura. @status', ['@status' => $response->getStatusCode()]);
      return [
        'success' => false,
        'message' => 'Error al enviar la factura.',
      ];
    }
  }

}
