<?php

namespace Drupal\password_enhancements\Plugin;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines required methods for a password constraint plugin.
 */
interface PasswordConstraintPluginInterface {

  /**
   * The default singular description text for the password requirements.
   *
   * @return string
   *   The default singular description for the password constraint.
   */
  public function defaultDescriptionSingular(): string;

  /**
   * The default plural description text for the password requirements.
   *
   * @return string
   *   The default plural description for the password constraint.
   */
  public function defaultDescriptionPlural(): string;

  /**
   * Gets the initial description.
   *
   * @return string
   *   The initial singular or plural description.
   */
  public function getInitialDescription(): string;

  /**
   * Password constrains settings form.
   *
   * @return array
   *   Renderable array.
   */
  public function settingsForm(): array;

  /**
   * Validate submitted values through the form.
   *
   * @param array $form
   *   The renderable settings form reference.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form's state.
   */
  public function settingsValidate(array &$form, FormStateInterface $form_state): void;

  /**
   * Submit callback.
   *
   * @param array $form
   *   The renderable settings form reference.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form's state.
   */
  public function settingsSubmit(array &$form, FormStateInterface $form_state): void;

  /**
   * Validation callback for the constraint.
   *
   * @param string $value
   *   The value that needs to be validated.
   *
   * @throws \Drupal\password_enhancements\Plugin\Exception\PasswordConstraintPluginValidationException
   *   The exception will be thrown if the validation fails.
   */
  public function validate(string $value): void;

}
