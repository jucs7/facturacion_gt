<?php

namespace Drupal\facturacion_gt\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandlerBase;

/**
 * Webform handler para agregar productos.
 *
 * @WebformHandler(
 *   id = "agregar_producto_handler",
 *   label = "Agregar Producto Handler",
 *   category = "Content",
 *   description = "Agrega un nuevo producto en el sistema.",
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class ProductoWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, \Drupal\webform\WebformSubmissionInterface $webform_submission) {
    $data = $webform_submission->getData();

    $nombre = $data['nombre'];
    $codigo = $data['codigo'];
    $cantidad_de_stock = $data['cantidad_de_stock'];
    $precio = $data['precio'];

    // Verifica si ya existe un producto con el mismo codigo.
    $existing_nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'producto', 'field_codigo' => $codigo]);

    if (!empty($existing_nodes)) {
      \Drupal::messenger()->addError('Ya existe un producto con el codigo');
      return;
    }

    // Crear el contenido de tipo prducto
    $node = Node::create([
      'type' => 'producto',
      'title' => $nombre,
      'field_codigo' => $codigo,
      'field_nombre' => $nombre,
      'field_stock' => $cantidad_de_stock,
      'field_precio' => $precio,
      'status' => 1,
    ]);

    $node->save();

    \Drupal::messenger()->addStatus('El producto fue agregado correctamente');
  }
}
