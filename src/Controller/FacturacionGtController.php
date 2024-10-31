<?php

namespace Drupal\facturacion_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;

class FacturacionGtController extends ControllerBase {
  public function renderFacturarTemplate() {
    // Cargar webform de facturar
    $webform = Webform::load('facturar');

    // Renderizar el webform
    $webform_render = [
      '#type' => 'webform',
      '#webform' => $webform,
    ];
    
    return [
      '#theme' => 'webform_facturar',
      '#webform' => $webform_render,
    ];
  }
}