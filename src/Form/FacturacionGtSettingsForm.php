<?php

namespace Drupal\facturacion_gt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FacturacionGtSettingsForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facturacion_gt_settings_form';
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'facturacion_gt.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('facturacion_gt.settings');

    $form['resolution']['resolutionRangeInitial'] = [
      '#type' => 'number',
      '#title' => 'Rango inicial',
      '#description' => 'Rango inicial de facturación',
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['resolution']['resolutionRangeFinal'] = [
      '#type' => 'number',
      '#title' => 'Rango final',
      '#description' => 'Rango final de facturación',
      '#min' => 0,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('facturacion_gt.settings')
      ->set('resolutionRangeInitial', $form_state->getValue('resolutionRangeInitial'))
      ->set('resolutionRangeFinal', $form_state->getValue('resolutionRangeFinal'))
      ->set('consecutive', $form_state->getValue('resolutionRangeInitial'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}