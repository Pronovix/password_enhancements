<?php

namespace Drupal\password_enhancements;

use Drupal\Component\Render\FormattableMarkup;

/**
 * Defines a password constraint service.
 *
 * @internal
 */
final class PasswordConstraintService {

  /**
   * The password policy manager.
   *
   * @var \Drupal\password_enhancements\PasswordPolicyManagerService
   */
  private $policyManager;

  /**
   * PasswordConstraintService constructor.
   *
   * @param \Drupal\password_enhancements\PasswordPolicyManagerService $policy_manager
   *   The password policy manager.
   */
  public function __construct(PasswordPolicyManagerService $policy_manager) {
    $this->policyManager = $policy_manager;
  }

  /**
   * Returns the password rules for a set of roles.
   *
   * @param string[] $roles
   *   Array of role IDs.
   *
   * @return array
   *   An array with two values:
   *   - First one is an integer: the number of minimum required constraints for
   *     the given roles.
   *   - Second one is an array of password constraints for the given roles.
   */
  public function getPasswordRulesForRoles(array $roles): array {
    $minimum_required_constraints = NULL;
    // Retrieve a list of policies ordered by their role's weights.
    /** @var \Drupal\password_enhancements\PasswordPolicyInterface[] $policies */
    $policies = $this->policyManager->loadMultiplePoliciesByRoles($roles);

    // Prepare a list of constraints ordered by their role weights of their
    // policies.
    $constraint_list = [];
    $processed_constraints = [];
    // If a constraint is defined in a higher priority (heavier) role, then the
    // constraints defined on the lower priority (lighter) role will be
    // overridden based on their type.
    // Non-unique constraints from the same policy/role won't override each
    // other, although if a higher priority policy's constraint defines that
    // specific type then it will override any and all non-unique constraint
    // from the lower priority from the same type.
    foreach ($policies as $policy) {
      if ($minimum_required_constraints === NULL) {
        $minimum_required_constraints = $policy->getMinimumRequiredConstraints();
      }
      $processed_constraints_by_policy = [];
      foreach ($policy->getConstraints() as $constraint) {
        $type = $constraint->getPluginId();
        // If this type of constraint hasn't been added to the to-be-returned
        // constraint list, add it and remember that it was processed on this
        // policy.
        if (!isset($processed_constraints[$type])) {
          $processed_constraints_by_policy[$type] = TRUE;
          $constraint_list[$constraint->getUuid()] = $constraint;
        }
      }
      // Only update the list of processed constraints _after_ processing _all_
      // the constraints of the policy - to make sure all non-unique constraints
      // of it are added.
      $processed_constraints += $processed_constraints_by_policy;
    }

    return [$minimum_required_constraints, $constraint_list];
  }

  /**
   * Gets constraint plugin settings.
   *
   * @param array $roles
   *   The roles for which the plugin settings needs to be loaded.
   *
   * @return array
   *   The constraint plugin settings.
   */
  public function getPluginSettings(array $roles): array {
    // PHPCS has a hard time understanding this syntax of assignment.
    // phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
    /** @var \Drupal\password_enhancements\PasswordConstraintInterface[] $constraints */
    [$minimum_required_constraints, $constraints] = $this->getPasswordRulesForRoles($roles);

    $processed_constraints = [];
    $libraries = [];
    $plugin_configs = [];
    foreach ($constraints as $uuid => $constraint) {
      $uuid = $constraint->getUuid();
      $type = $constraint->getPluginId();

      // Get JS library if set.
      $plugin_definition = $constraint->getPluginDefinition();
      if (!empty($plugin_definition['jsLibrary'])) {
        $libraries[] = $plugin_definition['jsLibrary'];
      }
      $plugin_configs[$type][$uuid] = $constraint->getConfiguration()['data'];

      $arguments = [];
      foreach ($constraint->getConfiguration()['data'] as $key => $value) {
        $arguments["@{$key}"] = new FormattableMarkup('<span data-key="@key">@value</span>', [
          '@key' => $key,
          '@value' => $value,
        ]);
      }

      $processed_constraints[] = [
        '#theme' => 'password_enhancements_policy_constraint',
        '#description' => strtr($constraint->getInitialDescription(), $arguments),
        '#attributes' => [
          'id' => $uuid,
          'class' => ['constraint'],
          'data-required' => $constraint->isRequired() ? 'yes' : 'no',
          'data-validation-passed' => 'no',
          'data-constraint' => $constraint->getPluginId(),
        ],
      ];
      unset($constraints[$uuid]);
    }

    return [
      'configs' => $plugin_configs,
      'constraints' => $processed_constraints,
      'libraries' => $libraries,
      'minimumRequiredConstraints' => $minimum_required_constraints,
      // phpcs:enable
    ];
  }

}
