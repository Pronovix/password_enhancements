<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Password Constraint config entity.
 *
 * @ConfigEntityType(
 *   id = "password_enhancements_constraint",
 *   label = @Translation("Password constraint"),
 *   label_collection = @Translation("Password constraints"),
 *   handlers = {
 *     "storage" = "Drupal\password_enhancements\Entity\Storage\ConstraintEntityStorage",
 *     "route_provider" = {
 *       "html" = "Drupal\password_enhancements\Entity\Routing\ConstraintHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\password_enhancements\Form\ConstraintForm",
 *       "delete" = "Drupal\password_enhancements\Form\ConstraintDeleteForm",
 *     },
 *     "list_builder" = "Drupal\password_enhancements\Entity\ConstraintListBuilder",
 *   },
 *   config_prefix = "constraint",
 *   admin_permission = "administer user password settings",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   config_export = {
 *     "id",
 *     "type",
 *     "status",
 *     "descriptionSingular",
 *     "descriptionPlural",
 *     "policy",
 *     "required",
 *     "settings",
 *   },
 *   links = {
 *     "collection" = "/admin/config/people/password/policy/{password_enhancements_policy}/constraints",
 *     "add-form" = "/admin/config/people/password/policy/{password_enhancements_policy}/constraint/add",
 *     "edit-form" = "/admin/config/people/password/policy/{password_enhancements_policy}/constraint/{password_enhancements_constraint}",
 *     "delete-form" = "/admin/config/people/password/policy/{password_enhancements_policy}/constraint/{password_enhancements_constraint}/delete",
 *   }
 * )
 */
class Constraint extends ConfigEntityBase implements ConstraintInterface {

  /**
   * Singular description for the password field.
   *
   * @var string
   */
  public $descriptionSingular;

  /**
   * Plural description for the password field.
   *
   * @var string
   */
  public $descriptionPlural;

  /**
   * Entity ID.
   *
   * @var string
   */
  public $id;

  /**
   * The password policy config ID where the constraint belongs to.
   *
   * @var string
   */
  public $policy;

  /**
   * Whether the constraint is required or can be marked as optional.
   *
   * @var bool
   */
  public $required;

  /**
   * Extra settings for the constraint.
   *
   * @var string[]
   */
  public $settings;

  /**
   * The type of the password constraint plugin.
   *
   * @var string
   */
  public $type;

  /**
   * {@inheritdoc}
   */
  public function getConfiguration(): array {
    return $this->settings + [
      'descriptionSingular' => $this->getDescriptionSingular(),
      'descriptionPlural' => $this->getDescriptionPlural(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescriptionSingular(): ?string {
    return $this->descriptionSingular;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescriptionPlural(): ?string {
    return $this->descriptionPlural;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getType() . ' (' . $this->getPolicy() . ')';
  }

  /**
   * {@inheritdoc}
   */
  public function getPolicy(): ?string {
    return $this->policy;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting(string $setting): ?string {
    return $this->settings[$setting] ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(): array {
    return $this->settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): ?string {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired(): bool {
    return $this->required ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescriptionSingular(string $description): ConstraintInterface {
    $this->descriptionSingular = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescriptionPlural(string $description): ConstraintInterface {
    $this->descriptionPlural = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPolicy(string $policy): ConstraintInterface {
    $this->policy = $policy;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired(bool $required): ConstraintInterface {
    $this->required = $required;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings): ConstraintInterface {
    $this->settings = $settings;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSetting(string $setting, $value): ConstraintInterface {
    $this->settings[$setting] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setType(string $type): ConstraintInterface {
    $this->type = $type;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function urlRouteParameters($rel): array {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if (!empty($this->policy)) {
      $uri_route_parameters['password_enhancements_policy'] = $this->policy;
    }

    return $uri_route_parameters;
  }

}
