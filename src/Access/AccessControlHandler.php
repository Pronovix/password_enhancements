<?php

namespace Drupal\password_enhancements\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Drupal\password_enhancements\PasswordPolicy;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines access checks for the password_enhancements module.
 */
class AccessControlHandler implements ContainerInjectionInterface {

  /**
   * User entity storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $constraintPluginManager;

  /**
   * Constructs the access control handler.
   *
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user entity storage.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $constraint_plugin_manager
   *   The constraint plugin manager.
   */
  public function __construct(UserStorageInterface $user_storage, PasswordConstraintPluginManager $constraint_plugin_manager) {
    $this->userStorage = $user_storage;
    $this->constraintPluginManager = $constraint_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('plugin.manager.password_enhancements.constraint')
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
   * Check whether the given role can have one more given password constraint.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function hasConstraintAddAccess(RouteMatchInterface $current_route_match, AccountInterface $current_user): AccessResultInterface {
    $allowed = $current_user->hasPermission('administer user password enhancements settings');
    $constraint_type = $current_route_match->getParameter('password_constraint');
    $policy = PasswordPolicy::createFromRole($this->constraintPluginManager, $current_route_match->getParameter('user_role'));
    // User can add the given constraint to the given role with a policy when:
    // - the constraint type is not unique,
    // - OR the policy doesn't have this type of a constraint yet.
    if ($policy) {
      $allowed = TRUE;
      if ($constraint_type['unique']) {
        /** @var \Drupal\password_enhancements\Plugin\PasswordConstraint\PasswordConstraintBase $policy_constraint */
        foreach ($policy->getConstraints() as $policy_constraint) {
          if ($policy_constraint->getPluginId() === $constraint_type['id']) {
            $allowed = FALSE;
            break;
          }
        }
      }
    }
    return AccessResult::allowedIf($allowed);
  }

}
