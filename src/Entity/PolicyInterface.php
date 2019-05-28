<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines required methods for the password policy config entity.
 */
interface PolicyInterface extends EntityInterface {

  /**
   * Gets the expiry in days.
   *
   * @return int|null
   *   The expiry in days.
   */
  public function getExpireDays(): ?int;

  /**
   * Gets the name of the policy.
   *
   * @return string|null
   *   The name of the policy.
   */
  public function getName(): ?string;

  /**
   * Gets the minimally required constraint number.
   *
   * @return int|null
   *   The minimally required constraint number.
   */
  public function getMinimumRequiredConstraints(): ?int;

  /**
   * Gets the priority of the policy.
   *
   * @return int|null
   *   The policy's priority.
   */
  public function getPriority(): ?int;

  /**
   * Gets the related user role.
   *
   * @return string|null
   *   The user role.
   */
  public function getRole(): ?string;

  /**
   * Sets the expiry in days.
   *
   * @param int $days
   *   The expiry in days.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   */
  public function setExpireDays(int $days): PolicyInterface;

  /**
   * Sets the name of the policy.
   *
   * @param string $name
   *   The name of the policy.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   */
  public function setName(string $name): PolicyInterface;

  /**
   * Sets the minimally required constraints.
   *
   * @param int $number
   *   The number of the minimally required constraints.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   */
  public function setMinimumRequiredConstraints(int $number): PolicyInterface;

  /**
   * Sets the policy's priority.
   *
   * @param int $priority
   *   The priority of the policy.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   */
  public function setPriority(int $priority): PolicyInterface;

  /**
   * Sets the user role.
   *
   * @param string $role
   *   The user role.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   */
  public function setRole(string $role): PolicyInterface;

}
