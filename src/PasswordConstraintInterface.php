<?php

namespace Drupal\password_enhancements;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for password constraints.
 */
interface PasswordConstraintInterface extends PluginInspectionInterface, ConfigurableInterface, DependentPluginInterface {

  /**
   * Validates a password string against the constraint.
   *
   * @param string $value
   *   The value that needs to be validated.
   *
   * @throws \Drupal\password_enhancements\Plugin\Exception\PasswordConstraintPluginValidationException
   *   The exception will be thrown if the validation fails.
   */
  public function validate(string $value): void;

  /**
   * Returns a render array summarizing the config of the password constraint.
   *
   * @return array
   *   A render array.
   */
  public function getSummary(): array;

  /**
   * Returns the password constraint name.
   *
   * @return string
   *   The password constraint name.
   */
  public function name(): string;

  /**
   * Returns the unique ID representing the password constraint.
   *
   * @return string
   *   The password constraint ID.
   */
  public function getUuid(): string;

  /**
   * Returns whether the constraint is required or not.
   *
   * @return bool
   *   TRUE if the constraint is required, FALSE otherwise.
   */
  public function isRequired(): bool;

  /**
   * Builds the configuration form for the password constraint.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The built form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array;

  /**
   * Validates the configuration form for the password constraint.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void;

  /**
   * Handles the password constraint configuration form submission.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void;

  /**
   * Returns the initial description for the constraint.
   *
   * @return string
   *   The initial description for the constraint.
   */
  public function getInitialDescription(): string;

}
