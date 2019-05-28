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
   * The generated error message.
   *
   * @return string|null
   *   The error message or NULL if there was none.
   */
  public function getErrorMessage(): ?string;

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
   * @return bool
   *   Returns TRUE if the constraint passes, error message otherwise.
   */
  public function validate(string $value);

}
