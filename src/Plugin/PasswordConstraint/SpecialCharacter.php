<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;

/**
 * Special character password constraint plugin.
 *
 * @PasswordConstraint(
 *   id = "special_character",
 *   name = @Translation("Special character"),
 *   description = @Translation("Checks if the password has at least a specified number of special character."),
 *   unique = FALSE,
 *   jsLibrary = "password_enhancements/plugin.special_character",
 * )
 */
final class SpecialCharacter extends MinimumCharacters {

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionSingular(): string {
    return 'Add at least one special character.';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultDescriptionPlural(): string {
    return 'Add @minimum_characters more special characters.';
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(): array {
    $form = parent::settingsForm();

    $form['use_custom_special_characters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Define special characters'),
      '#description' => $this->t('If no custom special characters are defined, then all non-alphanumeric characters will be checked as special.'),
      '#default_value' => !empty($this->configuration['use_custom_special_characters']) ? $this->configuration['use_custom_special_characters'] : 0,
    ];

    $form['special_characters'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Special characters'),
      '#description' => $this->t('Defines which special characters should be checked in the password, alphanumeric characters are not allowed.'),
      '#default_value' => !empty($this->configuration['special_characters']) ? $this->configuration['special_characters'] : ' !"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~',
      '#states' => [
        'visible' => [
          ':input[name="settings[use_custom_special_characters]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValidate(array &$form, FormStateInterface $form_state): void {
    parent::settingsValidate($form, $form_state);

    if (empty($form_state->getValue(['settings', 'use_custom_special_characters']))) {
      $input = &$form_state->getUserInput();
      unset($input['settings']['special_characters']);
      $form_state->setValue(['settings', 'special_characters'], NULL);
      $form_state->setValue(['settings', 'use_custom_special_characters'], 0);
    }
    elseif (preg_match('/[a-z0-9]/i', $form_state->getValue(['settings', 'special_characters']))) {
      $form_state->setError($form['settings']['special_characters'], $this->t('Alphanumeric characters are not allowed.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value): bool {
    if ($this->configuration['use_custom_special_characters']) {
      $special_characters = preg_quote($this->configuration['special_characters'], '/');
      $result = !empty($special_characters) ? preg_replace("/([^{$special_characters}])/", '', $value) : '';
    }
    else {
      $result = preg_replace('/([a-z0-9])/i', '', $value);
    }

    $is_valid = parent::validate($result ?? "", $this->configuration['use_custom_special_characters']);

    if (!$is_valid && $this->configuration['use_custom_special_characters']) {
      $count = $this->configuration['minimum_characters'] - mb_strlen($result);
      $this->errorMessage = $this->formatPlural($count, $this->configuration['descriptionSingular'], $this->configuration['descriptionPlural'], [
        '@minimum_characters' => $count,
        '@special_characters' => $this->configuration['special_characters'],
      ]);
    }

    return $is_valid;
  }

}
