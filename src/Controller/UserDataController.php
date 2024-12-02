<?php

namespace Drupal\facturacion_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;

class UserDataController extends ControllerBase {
  public function getUserData($uid) {
    $user = user_load_by_name($uid);
    $user_id = $user;
    if ($user_id) {
      return new JsonResponse([
        'field_nombre_completo' => $user->get('field_nombre_completo')->value,
        'field_identificacion' => $user->get('field_identificacion')->value,
        'field_tipo_de_persona' => $user->get('field_tipo_de_persona')->value,
      ]);
    }

    return new JsonResponse([], 404);
  }
}
