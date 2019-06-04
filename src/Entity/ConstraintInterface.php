<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines required methods for the password constraint config entity.
 */
interface ConstraintInterface extends EntityInterface {

  /**
   * Gets the configuration required for initializing a new plugin instance.
   *
   * @return array
   *   Configuration for the password constraint plugin instance.
   */
  public function getConfiguration(): array;

  /**
   * Gets singular description.
   *
   * @return string|null
   *   The constraint's singular description.
   */
  public function getDescriptionSingular(): ?string;

  /**
   * Gets plural description.
   *
   * @return string|null
   *   The constraint's plural description.
   */
  public function getDescriptionPlural(): ?string;

  /**
   * Gets the related password policy config entity ID.
   *
   * @return string|null
   *   The password policy config entity ID.
   */
  public function getPolicy(): ?string;

  /**
   * Gets a specific setting.
   *
   * @param string $setting
   *   The requested setting's key.
   *
   * @return string|null
   *   The requested setting or NULL if it cannot be found.
   */
  public function getSetting(string $setting): ?string;

  /**
   * Gets the settings.
   *
   * @return array
   *   A list of settings.
   */
  public function getSettings(): array;

  /**
   * Gets the constraints plugin ID.
   *
   * @return string
   *   Password constraint plugin ID.
   */
  public function getType(): ?string;

  /**
   * Gets whether the constraint is required or can be marked as optional.
   *
   * @return bool
   */
  public function isRequired(): bool;

  /**
   * Sets singular description.
   *
   * @param string $description
   *   The singular description that needs to be set.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   *   The current object.
   */
  public function setDescriptionSingular(string $description): ConstraintInterface;

  /**
   * Sets plural description.
   *
   * @param string $description
   *   The plural description that needs to be set.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   *   The current object.
   */
  public function setDescriptionPlural(string $description): ConstraintInterface;

  /**
   * Sets the password policy config entity ID.
   *
   * @param string $policy
   *   The password policy config entity ID.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   *   The current object.
   */
  public function setPolicy(string $policy): ConstraintInterface;

  /**
   * Sets whether the constraint should be required or it can be optional.
   *
   * @param bool $required
   *   TRUE if the constraint is required, FALSE if can be marked as optional.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   */
  public function setRequired(bool $required): ConstraintInterface;

  /**
   * Sets settings.
   *
   * @param array $settings
   *   The settings array keyed by the setting's name.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   *   The current object.
   */
  public function setSettings(array $settings): ConstraintInterface;

  /**
   * Sets a setting.
   *
   * @param string $setting
   *   The name of the setting that needs to be set.
   * @param mixed $value
   *   The setting's value.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   *   The current object.
   */
  public function setSetting(string $setting, $value): ConstraintInterface;

  /**
   * Sets the constraint's plugin ID.
   *
   * @param string $type
   *   Constraint plugin ID.
   *
   * @return \Drupal\password_enhancements\Entity\ConstraintInterface
   *   The current object.
   */
  public function setType(string $type): ConstraintInterface;

}
