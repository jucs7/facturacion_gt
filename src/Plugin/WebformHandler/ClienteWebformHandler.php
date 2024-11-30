<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\user\Entity\User;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Webform handler para registrar clientes.
 *
 * @WebformHandler(
 *   id = "registrar_cliente_handler",
 *   label = "Registrar Cliente Handler",
 *   category = "User",
 *   description = "Registra un nuevo cliente en el sistema.",
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class ClienteWebformHandler extends WebformHandlerBase {

  public function postSave(WebformSubmissionInterface $webform_submission, $update = FALSE) {
    // Obtener los datos del formulario.
    $data = $webform_submission->getData();

    // Verificar si ya existe un cliente con la misma identificacion.
    $existing_user = user_load_by_name($data['identificacion']);
    if ($existing_user) {
      if ($existing_user->hasRole('cliente')) {
        \Drupal::messenger()->addMessage('El cliente ya esta registrado.', 'error');
        return;
      }
    }

    // Crear el nuevo cliente.
    $user = User::create();
    $user->setUsername($data['identificacion']); // Identificación como username.
    $user->setEmail($data['email']);
    $user->setPassword("clientecomercio"); // Generar una contraseña segura.

    // Agregar roles.
    $user->addRole('cliente'); // Asegúrate de que este rol exista.

    // Agregar campos personalizados.
    $user->set('field_tipo_de_persona', $data['tipo_de_persona']);
    $user->set('field_tipo_de_identificacion', $data['tipo_de_identificacion']);
    $user->set('field_tipo_de_regimen', $data['tipo_de_regimen']);
    $user->set('field_responsabilidades_fiscales', $data['responsabilidades_fiscales']);

    if ($data['tipo_de_persona'] == '1') {
      $user->set('field_razon_social', $data['razon_social']);
      $user->set('field_nombre_completo', $data['razon_social']);
    } else if ($data['tipo_de_persona'] == '2') {
      $user->set('field_nombres', $data['nombres']);
      $user->set('field_apellidos', $data['apellidos']);
      $user->set('field_nombre_completo', $data['nombres'] . ' ' . $data['apellidos']);
    }

    if ($data['tipo_de_identificacion'] == '31') {
      $nit = explode("-", $data['identificacion']);
      $user->set('field_identificacion', $nit[0]);
      $user->set('field_digito_de_verificacion', $nit[1]);
    } else {
      $user->set('field_identificacion', $data['identificacion']);
    }
    
    $user->set('field_email', $data['email']);
    $user->set('field_telefono', $data['telefono']);

    // Guardar el usuario.
    try {
      $user->save();
      \Drupal::messenger()->addMessage('Cliente registrado exitosamente.');
    }
    catch (\Exception $e) {
      \Drupal::logger('facturacion_gt')->error($e->getMessage());
      \Drupal::messenger()->addMessage('Ocurrió un error al registrar el cliente.', 'error');
    }
  }
  
}
