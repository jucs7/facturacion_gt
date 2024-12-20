<?php

namespace Drupal\facturacion_gt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FacturacionGtInvoiceSettingsForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facturacion_gt_invoice_settings_form';
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'facturacion_gt.invoice_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('facturacion_gt.invoice_settings');

    $form['entorno'] = [
      '#type' => 'fieldset',
      '#title' => 'Entorno',
      '#description' => 'Entorno de operación',
    ];

    $form['entorno']['endpoint'] = [
      '#type' => 'textfield',
      '#title' => 'Endpoint',
      '#description' => 'Endpoint de facturación',
      '#default_value' => $config->get('endpoint'),
    ];

    $form['entorno']['apiKey'] = [
      '#type' => 'textfield',
      '#title' => 'Api Key',
      '#description' => 'X Api Key de autenticación',
      '#default_value' => $config->get('apiKey'),
    ];

    $form['resolution'] = [
      '#type' => 'fieldset',
      '#title' => 'Resolución',
      '#description' => 'Datos de la resolución',
    ];
    
    $form['resolution']['resolutionKey'] = [
      '#type' => 'textfield',
      '#title' => 'Clave de resolución',
      '#description' => 'Clave técnica de resolución de numeración de la DIAN',
      '#default_value' => $config->get('resolutionKey'),
    ];
    
    $form['resolution']['resolutionPrefix'] = [
      '#type' => 'textfield',
      '#title' => 'Prefijo de resolución',
      '#description' => 'Prefijo de numeración autorizado por la DIAN',
      '#default_value' => $config->get('resolutionPrefix'),
    ];
    
    $form['resolution']['resolutionNumber'] = [
      '#type' => 'number',
      '#title' => 'Número de resolución',
      '#description' => 'No. de la resolución',
      '#default_value' => $config->get('resolutionNumber'),
    ];

    $form['resolution']['resolutionRangeInitial'] = [
      '#type' => 'number',
      '#title' => 'Rango inicial',
      '#description' => 'Rango inicial de facturación',
      '#default_value' => $config->get('resolutionRangeInitial'),
      '#min' => 0,
    ];

    $form['resolution']['resolutionRangeFinal'] = [
      '#type' => 'number',
      '#title' => 'Rango final',
      '#description' => 'Rango final de facturación',
      '#default_value' => $config->get('resolutionRangeFinal'),
      '#min' => 0,
    ];

    $form['resolution']['consecutive'] = [
      '#type' => 'number',
      '#title' => 'Consecutivo',
      '#description' => 'Consecutivo de la siguiente factura',
      '#default_value' => $config->get('consecutive'),
      '#min' => 0,
    ];

    $form['resolution']['resolutionValidFrom'] = [
      '#type' => 'date',
      '#title' => 'Fecha inicial',
      '#description' => 'Fecha inicial de validación de facturación',
      '#default_value' => $config->get('resolutionValidFrom'),
    ];

    $form['resolution']['resolutionValidUntil'] = [
      '#type' => 'date',
      '#title' => 'Fecha final',
      '#description' => 'Fecha final de validación de facturación',
      '#default_value' => $config->get('resolutionValidUntil'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('facturacion_gt.invoice_settings')
      ->set('endpoint', $form_state->getValue('endpoint'))
      ->set('apiKey', $form_state->getValue('apiKey'))
      ->set('resolutionKey', $form_state->getValue('resolutionKey'))
      ->set('resolutionPrefix', $form_state->getValue('resolutionPrefix'))
      ->set('resolutionNumber', $form_state->getValue('resolutionNumber'))
      ->set('resolutionRangeInitial', $form_state->getValue('resolutionRangeInitial'))
      ->set('resolutionRangeFinal', $form_state->getValue('resolutionRangeFinal'))
      ->set('consecutive', $form_state->getValue('consecutive'))
      ->set('resolutionValidFrom', $form_state->getValue('resolutionValidFrom'))
      ->set('resolutionValidUntil', $form_state->getValue('resolutionValidUntil'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}