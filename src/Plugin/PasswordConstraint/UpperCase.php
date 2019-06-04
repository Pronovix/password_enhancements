<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

/**
 * Upper-case password constraint plugin.
 *
 * @PasswordConstraint(
 *   id = "upper_case",
 *   name = @Translation("Upper-case"),
 *   description = @Translation("Checks if the password has at least a specified number of upper-cased character."),
 *   unique = TRUE,
 *   jsLibrary = "password_enhancements/plugin.upper_case",
 * )
 */
final class UpperCase extends MinimumCharacters {

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionSingular(): string {
    return 'Add at least one upper-cased letter.';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionPlural(): string {
    return 'Add @minimum_characters more upper-cased letters.';
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value): void {
    parent::validate(preg_replace('/([^A-Z])/', '', $value));
  }

}
