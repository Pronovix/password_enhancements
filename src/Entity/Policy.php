<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Password Constraint config entity.
 *
 * @ConfigEntityType(
 *   id = "password_enhancements_policy",
 *   label = @Translation("Password policy"),
 *   label_collection = @Translation("Password policies"),
 *   handlers = {
 *     "storage" = "Drupal\password_enhancements\Entity\Storage\PasswordPolicyEntityStorage",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\password_enhancements\Form\PolicyForm",
 *       "delete" = "Drupal\password_enhancements\Form\PolicyDeleteForm",
 *     },
 *     "list_builder" = "Drupal\password_enhancements\Entity\PolicyListBuilder",
 *   },
 *   config_prefix = "policy",
 *   admin_permission = "administer user password settings",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   config_export = {
 *     "expireDays",
 *     "id",
 *     "name",
 *     "minimumRequiredConstraints",
 *     "status",
 *     "priority",
 *     "role",
 *   },
 *   links = {
 *     "collection" = "/admin/config/people/password/policies",
 *     "add-form" = "/admin/config/people/password/policy/add",
 *     "edit-form" = "/admin/config/people/password/policy/{password_enhancements_policy}",
 *     "delete-form" = "/admin/config/people/password/policy/{password_enhancements_policy}/delete",
 *   }
 * )
 */
class Policy extends ConfigEntityBase implements PolicyInterface {

  /**
   * Expiry of the password in days, 0 means that passwords doesn't expire.
   *
   * @var int
   */
  public $expireDays;

  /**
   * The ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the constraint.
   *
   * @var string
   */
  public $name;

  /**
   * The number of constraints that are required.
   *
   * @var int
   */
  public $minimumRequiredConstraints;

  /**
   * The priority of the policy.
   *
   * @var int
   */
  public $priority;

  /**
   * Role ID.
   *
   * @var string
   */
  public $role;

  /**
   * {@inheritdoc}
   */
  public function getExpireDays(): ?int {
    return $this->expireDays === "" ? NULL : $this->expireDays;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): ?string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getMinimumRequiredConstraints(): ?int {
    return $this->minimumRequiredConstraints;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority(): ?int {
    return $this->priority === "" ? NULL : $this->priority;
  }

  /**
   * {@inheritdoc}
   */
  public function getRole(): ?string {
    return $this->role;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpireDays(int $days): PolicyInterface {
    $this->expireDays = $days;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $name): PolicyInterface {
    $this->name = $name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMinimumRequiredConstraints(int $number): PolicyInterface {
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
