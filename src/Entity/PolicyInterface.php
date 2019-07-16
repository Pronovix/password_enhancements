<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines required methods for the password policy config entity.
 */
interface PolicyInterface extends EntityInterface {

  /**
   * Password with no expiry.
   *
   * @var int
   */
  const PASSWORD_NO_EXPIRY = 0;

  /**
   * Do not show error message.
   *
   * @var int
   */
  const PASSWORD_NO_WARNING = 0;

  /**
   * Gets the expiry in days.
   *
   * @return int
   *   The expiry in days.
   */
  public function getExpireDays(): int;

  /**
   * Gets the expiry in seconds.
   *
   * @return int
   *   The expiry in seconds.
   */
  public function getExpireSeconds(): int;

  /**
   * Gets how long before the warning message should be shown in seconds.
   *
   * @return int
   *   The seconds how long before the warning should be shown.
   */
  public function getExpireWarnSeconds(): int;

  /**
   * Gets the expire warning in days.
   *
   * @return int
   *   The expire warning in days.
   */
  public function getExpireWarnDays(): int;

  /**
   * Gets expiry warning message.
   *
   * @return string|null
   *   The expiry warning message.
   */
  public function getExpiryWarningMessage(): ?string;

  /**
   * Gets the minimally required constraint number.
   *
   * @return int
   *   The minimally required constraint number.
   */
  public function getMinimumRequiredConstraints(): int;

  /**
   * Gets the priority of the policy.
   *
   * @return int
   *   The policy's priority.
   */
  public function getPriority(): int;

  /**
   * Gets the related user role.
   *
   * @return string
   *   The user role.
   */
  public function getRole(): string;

  /**
   * Sets the expiry in seconds.
   *
   * @param int $seconds
   *   The expiry in seconds.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   *
   * @throws \Drupal\password_enhancements\Entity\Exception\PolicyInvalidArgumentException
   *   If the the given seconds value is negative.
   */
  public function setExpireSeconds(int $seconds): PolicyInterface;

  /**
   * Sets how long before the warning message should be shown in seconds.
   *
   * @param int $seconds
   *   The seconds how long before the warning message should be shown.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   *
   * @throws \Drupal\password_enhancements\Entity\Exception\PolicyInvalidArgumentException
   *   If the the given seconds value is negative.
   */
  public function setExpireWarnSeconds(int $seconds): PolicyInterface;

  /**
   * Sets expiry warning message.
   *
   * @param string|null $message
   *   The expiry warning message or NULL if none.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   */
  public function setExpiryWarningMessage(?string $message): PolicyInterface;

  /**
   * Sets the minimally required constraints.
   *
   * @param int $number
   *   The number of the minimally required constraints.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   The current object.
   *
   * @throws \Drupal\password_enhancements\Entity\Exception\PolicyInvalidArgumentException
   *   If the given number is negative.
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
