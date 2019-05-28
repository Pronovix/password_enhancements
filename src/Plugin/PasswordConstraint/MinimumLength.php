<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

/**
 * Upper-case password constraint plugin.
 *
 * @PasswordConstraint(
 *   id = "minimum_length",
 *   name = @Translation("Minimum length"),
 *   description = @Translation("Checks if the password has at least a specified number of character of any type."),
 *   unique = TRUE,
 *   jsLibrary = "password_enhancements/plugin.minimum_length",
 * )
 */
final class MinimumLength extends MinimumCharacters {

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionSingular(): string {
    return 'Add at least one letter.';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionPlural(): string {
    return 'Add @minimum_characters more letters.';
  }

}
