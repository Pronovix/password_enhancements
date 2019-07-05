<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

/**
 * Lower-case password constraint plugin.
 *
 * @PasswordConstraint(
 *   id = "lower_case",
 *   name = @Translation("Lower-case"),
 *   description = @Translation("Checks if the password has at least a specified number of lower-cased character."),
 *   unique = TRUE,
 *   jsLibrary = "password_enhancements/plugin.lower_case",
 * )
 */
final class LowerCase extends MinimumCharacters {

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionSingular(): string {
    return 'Add at least one lower-cased letter.';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionPlural(): string {
    return 'Add @minimum_characters more lower-cased letters.';
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value): void {
    parent::validate(preg_replace('/([^a-z])/', '', $value));
  }

}
