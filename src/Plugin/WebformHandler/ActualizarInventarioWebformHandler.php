<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\facturacion_gt\Handler\StockHandler;
use Drupal\facturacion_gt\Handler\InventarioNodeHandler;

/**
 * Webform submission handler for Facturacion GT.
 *
 * @WebformHandler(
 *   id = "actualizar_inventario_webform_handler",
 *   label = "Actualizar inventario Webform Handler",
 *   category = "Custom",
 *   description = "Actualizar inventario",
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class ActualizarInventarioWebformHandler extends WebformHandlerBase {
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $values = $webform_submission->getData();
    
    // Actualizar existencias en stock
    $stockHandler = new StockHandler($values['productos']);
    $stockHandler->actualizarStock($values['productos']);

    // Crear tipo de contenido
    $nodeHandler = new InventarioNodeHandler();
    $nodeHandler->createNode($values['productos']);
  }
}