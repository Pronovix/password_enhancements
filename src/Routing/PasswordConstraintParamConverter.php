<?php

namespace Drupal\password_enhancements\Routing;

use Drupal\password_enhancements\PasswordConstraintInterface;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Drupal\password_enhancements\PasswordPolicy;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Converts parameters for upcasting password constraint IDs to full objects.
 */
final class PasswordConstraintParamConverter implements ParamConverterInterface {

  /**
   * The role storage service.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  private $roleStorage;

  /**
   * The constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  private $constraintPluginManager;

  /**
   * Constructs a new PasswordConstraintParamConverter.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $constraint_plugin_manager
   *   The constraint plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PasswordConstraintPluginManager $constraint_plugin_manager) {
    $this->roleStorage = $entity_type_manager->getStorage('user_role');
    $this->constraintPluginManager = $constraint_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults): ?PasswordConstraintInterface {
    // Bail out early (with "Page not found") if:
    // ...the password constraint ID or the user role (ID) is missing.
    if (empty($value) || empty($defaults['user_role'])) {
      return NULL;
    }
    // ...the role could not be loaded (does not exist).
    /** @var \Drupal\user\RoleInterface $role */
    $role = $this->roleStorage->load($defaults['user_role']);
    if (!$role) {
      return NULL;
    }
    // ...the role has no password policy.
    $policy = PasswordPolicy::createFromRole($this->constraintPluginManager, $role);
    if (!$policy) {
      return NULL;
    }
    // ...the policy does not have the given constraint (ID).
    $constraints = $policy->getConstraints();
    if (!$constraints->has($value)) {
      return NULL;
    }
    // Do the actual conversion.
    return $constraints->get($value);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route): bool {
    return (!empty($definition['type']) && $definition['type'] === 'password_constraint');
  }

}
