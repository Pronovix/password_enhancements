<?php

namespace Drupal\password_enhancements\Entity\Storage;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines required methods for the password constraint entity storage.
 */
interface ConstraintEntityStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Load password policy constraints by the given role.
   *
   * @param string $role
   *   The role for which the constraints needs to be loaded.
   *
   * @return \Drupal\password_enhancements\Entity\Constraint[]
   *   Constraint list ordered by their policy's priority.
   */
  public function loadByRole(string $role): array;

  /**
   * Load password policy constraints by the given roles.
   *
   * @param array $roles
   *   The roles for which the constraints needs to be loaded.
   *
   * @return \Drupal\password_enhancements\Entity\Constraint[]
   *   Constraint list ordered by their policy's priority.
   */
  public function loadByRoles(array $roles): array;

}
