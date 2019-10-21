<?php

namespace Drupal\password_enhancements;

use Drupal\password_enhancements\Exception\PolicyInvalidArgumentException;
use Drupal\user\RoleInterface;

/**
 * Defines the Password Policy.
 */
final class PasswordPolicy implements PasswordPolicyInterface {

  /**
   * Expiry of the password in seconds, 0 means that passwords doesn't expire.
   *
   * @var int
   */
  private $expireSeconds;

  /**
   * Show warning message before the password would expire in a given seconds.
   *
   * @var int
   */
  private $expireWarnSeconds;

  /**
   * Expiry warning message.
   *
   * @var string|null
   */
  private $expiryWarningMessage;

  /**
   * The number of constraints that are required.
   *
   * @var int
   */
  private $minimumRequiredConstraints;

  /**
   * Role ID.
   *
   * @var string
   */
  private $role;

  /**
   * Holds the collection of password constraints that are used by this policy.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginCollection
   */
  private $constraints = [];

  /**
   * PasswordPolicy constructor.
   *
   * @param \Drupal\user\RoleInterface $role
   *   The role this Password Policy belongs to.
   * @param int $expire_seconds
   *   The expiry in seconds.
   * @param int $expire_warn_seconds
   *   The expiry warning in seconds.
   * @param string|null $expiry_warning_message
   *   The expiry warning message.
   * @param int $minimum_required_constraints
   *   The minimum number of required constraints.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginCollection $constraints
   *   The collection of constraints for the policy.
   *
   * @phpcs:disable DrupalPractice.Objects.UnusedPrivateMethod.UnusedMethod
   *   PHPCS fails to recognize that static::createFromRole() calls this.
   */
  private function __construct(RoleInterface $role, int $expire_seconds, int $expire_warn_seconds, ?string $expiry_warning_message, int $minimum_required_constraints, PasswordConstraintPluginCollection $constraints) {
    // @phpcs:enable
    $this->role = $role;
    // Set initial values.
    $this->expireSeconds = $expire_seconds;
    $this->expireWarnSeconds = $expire_warn_seconds;
    $this->expiryWarningMessage = $expiry_warning_message;
    $this->minimumRequiredConstraints = $minimum_required_constraints;
    $this->constraints = $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromRole(PasswordConstraintPluginManager $constraint_plugin_manager, RoleInterface $role): ?PasswordPolicyInterface {
    if ($third_party_settings = $role->getThirdPartySettings('password_enhancements')) {
      // Convert constraints array to collection here once and for all.
      $constraints = new PasswordConstraintPluginCollection($constraint_plugin_manager, $third_party_settings['constraints'] ?? []);
      return new static($role,
        $third_party_settings['expireSeconds'] ?? self::PASSWORD_NO_EXPIRY,
        $third_party_settings['expireWarnSeconds'] ?? self::PASSWORD_NO_WARNING,
        $third_party_settings['expiryWarningMessage'] ?? 'Your password will expire on @date_time, please <a href="@url">change your password</a> before it expires to prevent any potential data loss.',
        $third_party_settings['minimumRequiredConstraints'] ?? 1,
        $constraints);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getExpireDays(): int {
    return floor($this->getExpireSeconds() / 86400);
  }

  /**
   * {@inheritdoc}
   */
  public function getExpireSeconds(): int {
    return $this->expireSeconds;
  }

  /**
   * {@inheritdoc}
   */
  public function getExpireWarnSeconds(): int {
    return $this->expireWarnSeconds;
  }

  /**
   * {@inheritdoc}
   */
  public function getExpireWarnDays(): int {
    return floor($this->getExpireWarnSeconds() / 86400);
  }

  /**
   * {@inheritdoc}
   */
  public function getExpiryWarningMessage(): ?string {
    return $this->expiryWarningMessage;
  }

  /**
   * {@inheritdoc}
   */
  public function getMinimumRequiredConstraints(): int {
    return $this->minimumRequiredConstraints;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority(): int {
    return $this->role->getWeight();
  }

  /**
   * {@inheritdoc}
   */
  public function getRole(): RoleInterface {
    return $this->role;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraint($constraint): PasswordConstraintInterface {
    return $this->constraints->get($constraint);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints(): PasswordConstraintPluginCollection {
    return $this->constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpireSeconds(int $seconds): PasswordPolicyInterface {
    if ($seconds < 0) {
      throw new PolicyInvalidArgumentException('The expiry given in seconds must be a positive integer or zero.');
    }
    $this->expireSeconds = $seconds;
    // No need to store the expiry warning message if it's never to be shown.
    if ($seconds === 0) {
      $this->expiryWarningMessage = NULL;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpireWarnSeconds(int $seconds): PasswordPolicyInterface {
    if ($seconds < 0) {
      throw new PolicyInvalidArgumentException('The expiry warning in seconds must be a positive integer or zero.');
    }
    $this->expireWarnSeconds = $seconds;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpiryWarningMessage(?string $message): PasswordPolicyInterface {
    if ($this->expireSeconds > 0 && $this->expireWarnSeconds > 0 && !$message) {
      throw new PolicyInvalidArgumentException('Expiry warning message cannot be empty for expiring passwords.');
    }
    $this->expiryWarningMessage = $message;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMinimumRequiredConstraints(int $number): PasswordPolicyInterface {
    if ($number < 0) {
      throw new PolicyInvalidArgumentException('The minimum requirements cannot be less than zero.');
    }
    $this->minimumRequiredConstraints = $number;
    return $this;
  }

}
