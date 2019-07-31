<?php

namespace Drupal\password_enhancements;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\RoleInterface;

/**
 * Defines a password policy manager service.
 *
 * @internal
 */
final class PasswordPolicyManagerService {

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  private $uuidGenerator;

  /**
   * Role storage.
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
   * PasswordPolicyManagerService constructor.
   *
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The UUID generator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $constraint_plugin_manager
   *   The constraint plugin manager.
   */
  public function __construct(UuidInterface $uuid, EntityTypeManagerInterface $entity_type_manager, PasswordConstraintPluginManager $constraint_plugin_manager) {
    $this->uuidGenerator = $uuid;
    $this->roleStorage = $entity_type_manager->getStorage('user_role');
    $this->constraintPluginManager = $constraint_plugin_manager;
  }

  /**
   * Loads and sorts the password policies for the given roles.
   *
   * Sorting is done by the weight of the roles: the heavier role has higher
   * priority. Note that the Authenticated role is always considered as the one
   * with the lowest priority.
   *
   * @param array $roles
   *   An array of role IDs.
   *
   * @return \Drupal\password_enhancements\PasswordPolicyInterface[]
   *   An ordered list of \Drupal\password_enhancements\PasswordPolicyInterface
   *   objects.
   */
  public function loadMultiplePoliciesByRoles(array $roles): array {
    // Load all the roles asked for, but filter out the ones without policies.
    $role_objects = array_filter($this->roleStorage->loadMultiple($roles), function (RoleInterface $role) {
      return (bool) $role->getThirdPartySettings('password_enhancements');
    });

    // Sort the roles.
    usort($role_objects, function (RoleInterface $a, RoleInterface $b) {
      // Authenticated always comes last.
      if ($a->id() === RoleInterface::AUTHENTICATED_ID) {
        return 1;
      }
      if ($b->id() === RoleInterface::AUTHENTICATED_ID) {
        return -1;
      }
      // Everything else should come in the reverse order they're displayed in
      // the list of roles eg. on the /admin/people/roles page.
      $a_weight = $a->getWeight();
      $b_weight = $b->getWeight();
      return ($a_weight === $b_weight) ? ($a->id() < $b->id()) : ($a_weight < $b_weight);
    });

    // Prepare a list of policies ordered by their role's weights.
    $policies = [];
    foreach ($role_objects as $role) {
      $policy = PasswordPolicy::createFromRole($this->constraintPluginManager, $role);
      if ($policy) {
        $policies[] = $policy;
      }
    }
    return $policies;
  }

  /**
   * Loads the password policy for the highest priority (lightest) role.
   *
   * Note that the Authenticated role is always considered as the one with the
   * lowest priority.
   *
   * @param array $roles
   *   An array of role IDs.
   *
   * @return \Drupal\password_enhancements\PasswordPolicyInterface|null
   *   The password policy for the highest priority (lightest) role that has it,
   *   NULL when none of the given roles have a password policy.
   */
  public function loadPolicyByRoles(array $roles): ?PasswordPolicyInterface {
    $policies = $this->loadMultiplePoliciesByRoles($roles);
    return $policies ? reset($policies) : NULL;
  }

  /**
   * Adds a password constraint to a policy.
   *
   * @param \Drupal\password_enhancements\PasswordPolicyInterface $policy
   *   The password policy.
   * @param array $configuration
   *   An array of password constraint configuration.
   */
  public function addPasswordConstraint(PasswordPolicyInterface $policy, array $configuration): void {
    $configuration['uuid'] = $this->uuidGenerator->generate();
    $policy->getConstraints()->addInstanceId($configuration['uuid'], $configuration);
  }

  /**
   * Deletes a password constraint from a policy.
   *
   * @param \Drupal\password_enhancements\PasswordPolicyInterface $policy
   *   The password policy.
   * @param \Drupal\password_enhancements\PasswordConstraintInterface $constraint
   *   The password constraint object.
   */
  public function deletePasswordConstraint(PasswordPolicyInterface $policy, PasswordConstraintInterface $constraint): void {
    $policy->getConstraints()->removeInstanceId($constraint->getUuid());
    $this->savePolicy($policy);
  }

  /**
   * Saves a password policy to its role permanently.
   *
   * @param \Drupal\password_enhancements\PasswordPolicyInterface $policy
   *   The password policy.
   */
  public function savePolicy(PasswordPolicyInterface $policy): void {
    $constraints = [];
    foreach ($policy->getConstraints() as $constraint) {
      $constraints[$constraint->getUuid()] = [
        'uuid' => $constraint->getUuid(),
      ] + $constraint->getConfiguration();
    }
    // @TODO Should we set everything from the policy object?
    $role = $policy->getRole();
    if ($constraints) {
      $role->setThirdPartySetting('password_enhancements', 'constraints', $constraints);
    }
    else {
      $role->unsetThirdPartySetting('password_enhancements', 'constraints');
    }
    $role->save();
  }

}
