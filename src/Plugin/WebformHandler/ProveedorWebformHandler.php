<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\webform\Plugin\WebformHandlerBase;

/**
 * Webform handler para registrar proveedores.
 *
 * @WebformHandler(
 *   id = "registrar_proveedor_handler",
 *   label = "Registrar Proveedor Handler",
 *   category = "User",
 *   description = "Registra un nuevo proveedor en el sistema.",
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class ProveedorWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, \Drupal\webform\WebformSubmissionInterface $webform_submission) {
    $data = $webform_submission->getData();

    $nombre = $data['nombre_o_razon_social'];
    $email = $data['email'];
    $telefono = $data['telefono'];
    $tipo_identificacion = $data['tipo_de_identificacion'];
    $identificacion = $data['identificacion'];

    // Verifica si ya existe proveedor con la identificacion.
    $existing_users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['field_identificacion' => $identificacion]);

    foreach ($existing_users as $existing_user) {
      if ($existing_user->hasRole('proveedor')) {
        \Drupal::messenger()->addError('El proveedor ya esta registrado');
        return;
      }
    }

    // Crear el usuario proveedor.
    $user = User::create([
      'name' => $nombre,
      'mail' => $email,
      'status' => 1,
      'roles' => ['proveedor'],
    ]);

    $user->set('field_email', $email);
    $user->set('field_telefono', $telefono);
    $user->set('field_tipo_de_identificacion', $tipo_identificacion);
    $user->set('field_identificacion', $identificacion);

    $user->save();

    \Drupal::messenger()->addStatus('El proveedor fue registrado correctamente.');
  }
}
