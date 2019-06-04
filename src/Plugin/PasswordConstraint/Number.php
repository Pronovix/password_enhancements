<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

/**
 * Number password constraint plugin.
 *
 * @PasswordConstraint(
 *   id = "number",
 *   name = @Translation("Number"),
 *   description = @Translation("Checks if the password has at least a specified number of numbers."),
 *   unique = TRUE,
 *   jsLibrary = "password_enhancements/plugin.number",
 * )
 */
final class Number extends MinimumCharacters {

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionSingular(): string {
    return 'Add at least one number.';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionPlural(): string {
    return 'Add @minimum_characters more numbers.';
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value): void {
    parent::validate(preg_replace('/([^0-9])/', '', $value));
  }

}
