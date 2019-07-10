<?php

namespace Drupal\password_enhancements\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\password_enhancements\Entity\Storage\PolicyEntityStorageInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleStorageInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines access checks for the password_enhancements module.
 */
class AccessControlHandler implements ContainerInjectionInterface {

  /**
   * Policy entity storage.
   *
   * @var \Drupal\password_enhancements\Entity\Storage\PolicyEntityStorageInterface
   */
  protected $policyEntityStorage;

  /**
   * Role entity storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * User entity storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs the access control handler.
   *
   * @param \Drupal\password_enhancements\Entity\Storage\PolicyEntityStorageInterface $policy_entity_storage
   *   Policy entity storage.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   User entity storage.
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   Role entity storage.
   */
  public function __construct(PolicyEntityStorageInterface $policy_entity_storage, UserStorageInterface $user_storage, RoleStorageInterface $role_storage) {
    $this->policyEntityStorage = $policy_entity_storage;
    $this->roleStorage = $role_storage;
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('password_enhancements_policy'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')->getStorage('user_role')
    );
  }

  /**
   * Check whether the user has access to the password change page or not.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function hasPasswordChangeAccess(AccountInterface $current_user): AccessResultInterface {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->userStorage->load($current_user->id());
    $is_password_change_required = $user->get('password_enhancements_password_change_required')->getValue() ? (bool) $user->get('password_enhancements_password_change_required')->getValue()[0]['value'] : FALSE;
    return AccessResult::allowedIf($is_password_change_required);
  }

  /**
   * Checks whether the user can create any more policies or not.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function canCreatePolicy(AccountInterface $current_user): AccessResultInterface {
    $policies = $this->policyEntityStorage->getQuery()->count()->execute();
    $roles = $this->roleStorage->getQuery()->condition('id', Role::ANONYMOUS_ID, '<>')->count()->execute();
    return AccessResult::allowedIf($policies < $roles)->setCacheMaxAge(0);
  }

}
