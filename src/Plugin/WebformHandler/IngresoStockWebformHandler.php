<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\facturacion_gt\Handler\StockHandler;

/**
 * Webform submission handler for Facturacion GT.
 *
 * @WebformHandler(
 *   id = "ingreso_stock_webform_handler",
 *   label = "Ingreso de Stock Webform Handler",
 *   category = "Custom",
 *   description = "Maneja el ingreso de stock de productos",
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class IngresoStockWebformHandler extends WebformHandlerBase {

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
  
      $values = $webform_submission->getData();
      
      // Aumentar existencias en stock
      $stockHandler = new StockHandler($values['productos']);
      $stockHandler->ingresarStock($values['productos']);

    }
}