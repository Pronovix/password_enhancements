<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\password_enhancements\Entity\Exception\PolicyInvalidArgumentException;

/**
 * Defines the Password Policy config entity.
 *
 * @ConfigEntityType(
 *   id = "password_enhancements_policy",
 *   label = @Translation("Password policy"),
 *   label_collection = @Translation("Password policies"),
 *   handlers = {
 *     "storage" = "Drupal\password_enhancements\Entity\Storage\PolicyEntityStorage",
 *     "route_provider" = {
 *       "html" = "Drupal\password_enhancements\Entity\Routing\PolicyHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\password_enhancements\Form\PolicyForm",
 *       "delete" = "Drupal\password_enhancements\Form\PolicyDeleteForm",
 *     },
 *     "list_builder" = "Drupal\password_enhancements\Entity\PolicyListBuilder",
 *   },
 *   config_prefix = "policy",
 *   admin_permission = "administer user password enhancements settings",
 *   entity_keys = {
 *     "id" = "role",
 *     "label" = "role",
 *   },
 *   config_export = {
 *     "expireSeconds",
 *     "expireWarnSeconds",
 *     "expiryWarningMessage",
 *     "minimumRequiredConstraints",
 *     "status",
 *     "priority",
 *     "role",
 *   },
 *   links = {
 *     "collection" = "/admin/config/people/password-enhancements/policies",
 *     "add-form" = "/admin/config/people/password-enhancements/policy/add",
 *     "edit-form" = "/admin/config/people/password-enhancements/policy/{password_enhancements_policy}",
 *     "delete-form" = "/admin/config/people/password-enhancements/policy/{password_enhancements_policy}/delete",
 *   }
 * )
 */
class Policy extends ConfigEntityBase implements PolicyInterface {

  /**
   * Expiry of the password in seconds, 0 means that passwords doesn't expire.
   *
   * @var int
   */
  protected $expireSeconds;

  /**
   * Show warning message before the password would expire in a given seconds.
   *
   * @var int
   */
  protected $expireWarnSeconds;

  /**
   * Expiry warning message.
   *
   * @var string|null
   */
  protected $expiryWarningMessage;

  /**
   * The number of constraints that are required.
   *
   * @var int
   */
  protected $minimumRequiredConstraints;

  /**
   * The priority of the policy.
   *
   * @var int
   */
  protected $priority;

  /**
   * Role ID.
   *
   * @var string
   */
  protected $role;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    // Set initial values.
    $this->expireSeconds = static::PASSWORD_NO_EXPIRY;
    $this->expireWarnSeconds = static::PASSWORD_NO_WARNING;
    $this->minimumRequiredConstraints = 1;
    $this->priority = 1;
    $this->role = '';

    // The entity builder does not recognize the role configured as ID in some
    // places, so if the role is set, set it as the entity's ID.
    if (!empty($values['role'])) {
      $this->id = $values['role'];
    }

    parent::__construct($values, $entity_type);
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
    return $this->priority;
  }

  /**
   * {@inheritdoc}
   */
  public function getRole(): string {
    return $this->role;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpireSeconds(int $seconds): PolicyInterface {
    if ($seconds < 0) {
      throw new PolicyInvalidArgumentException('The expiry given in seconds must be a positive integer or zero.');
    }
    $this->expireSeconds = $seconds;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpireWarnSeconds(int $seconds): PolicyInterface {
    if ($seconds < 0) {
      throw new PolicyInvalidArgumentException('The expiry warning in seconds must be a positive integer or zero.');
    }
    $this->expireWarnSeconds = $seconds;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpiryWarningMessage(?string $message): PolicyInterface {
    $this->expiryWarningMessage = $message;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMinimumRequiredConstraints(int $number): PolicyInterface {
    if ($number < 0) {
      throw new PolicyInvalidArgumentException('The minimum requirements cannot be less than zero.');
    }
    $this->minimumRequiredConstraints = $number;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPriority(int $priority): PolicyInterface {
    $this->priority = $priority;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRole(string $role): PolicyInterface {
    $this->role = $role;
    return $this;
  }

}
