<?php

namespace Drupal\password_enhancements\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines access checks for the password_enhancements module.
 */
class AccessControlHandler implements AccessControlHandlerInterface, ContainerInjectionInterface {

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Constructs the access control.
   *
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session manager.
   */
  public function __construct(SessionManagerInterface $session_manager) {
    $this->sessionManager = $session_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('session_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasPasswordChangeAccess(AccountInterface $current_user): AccessResultInterface {
    $password_change_required = $this->sessionManager->getBag('attributes')
      ->getBag()
      ->get('password_enhancements_password_change_required');

    return AccessResult::allowedIf($password_change_required);
  }

}
