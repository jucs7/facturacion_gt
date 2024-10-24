<?php

namespace Drupal\facturacion_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Extension\ExtensionPathResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FacturacionGtController extends ControllerBase {

  protected $extensionPathResolver;

  // Constructor para inyectar el servicio de ExtensionPathResolver
  public function __construct(ExtensionPathResolver $extensionPathResolver) {
    $this->extensionPathResolver = $extensionPathResolver;
  }

  // Método estático para crear la instancia del controlador
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.path.resolver')
    );
  }

  public function enviarFactura(Request $request) {
    // Obtener la ruta del módulo utilizando el servicio extension.path.resolver.
    $module_path = $this->extensionPathResolver->getPath('module', 'facturacion_gt');

    // Ruta completa al archivo JSON
    $json_file = $module_path . '/config/factura.json';

    // Verificar si el archivo existe y leerlo
    if (file_exists($json_file)) {
      $factura_data = json_decode(file_get_contents($json_file), TRUE);
    } else {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'No se pudo encontrar el archivo de factura.',
      ]);
    }

    // URL de la API de facturación electrónica
    $api_url = 'https://isv.aliaddo.net/api/v1/public/documents/invoice/test';

    // Crear una instancia de GuzzleHttp Client
    $client = new Client();

    try {
      // Realizar request por metodo POST
      $response = $client->post($api_url, [
        'json' => $factura_data,
        // Adicionar cabeceras de autenticación con token Bearer
        'headers' => [
          'x-api-key' => 'key-3440faa4a3b2419aa1c3ac3b2a457b8d-031610',
          'content-type' => 'application/json',
          'cache-control' => 'no-cache',
          'accept' => 'application/json',
        ],
      ]);

      // Decodificar la respuesta de la API
      $body = json_decode($response->getBody(), TRUE);

      // Mostar respuesta o error si hubo alguno
      if ($response->getStatusCode() == 200) {
        return new JsonResponse([
          'status' => 'success',
          'message' => 'Factura enviada con éxito',
          'response' => $body,
        ]);
        \Drupal::logger('facturacion_gt')->notice('Se ha enviado la factura correctamente.');
      }
      else {
        return new JsonResponse([
          'status' => 'error',
          'message' => 'Error al enviar la factura',
          'response' => $body,
        ]);
        \Drupal::logger('facturacion_gt')->notice('Error al enviar la factura.');
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
