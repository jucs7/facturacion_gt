<?php

namespace Drupal\facturacion_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;

class FacturacionGtController extends ControllerBase {
  
  protected $current_user;
  protected $request_stack;

  public function __construct(AccountInterface $current_user, RequestStack $request_stack) {
    $this->current_user = $current_user;
    $this->request_stack = $request_stack;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('request_stack')
    );
  }

  // Cargar webform de facturar
  public function renderFacturarTemplate() {

    // Verifica si el usuario está autenticado.
    if (!$this->current_user->isAuthenticated()) {
      // Redirige al login si no está autenticado.
      $login_url = Url::fromRoute('user.login', [], [
        'query' => ['destination' => $this->request_stack->getCurrentRequest()->getPathInfo()],
      ])->toString();
      return new RedirectResponse($login_url);
    }

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