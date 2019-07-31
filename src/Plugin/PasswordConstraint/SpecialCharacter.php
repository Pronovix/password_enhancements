<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\password_enhancements\Plugin\Exception\PasswordConstraintPluginValidationException;

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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    // @TODO: Document the availability of the @special_characters placeholder.
    $form['use_custom_special_characters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Define special characters'),
      '#description' => $this->t('If no custom special characters are defined then all non-alphanumeric characters will be checked as special.'),
      '#default_value' => !empty($this->configuration['data']['use_custom_special_characters']) ? $this->configuration['data']['use_custom_special_characters'] : 0,
    ];

    $form['special_characters'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Special characters'),
      '#description' => $this->t('Defines which special characters should be checked in the password, alphanumeric characters are not allowed.'),
      '#default_value' => !empty($this->configuration['data']['special_characters']) ? $this->configuration['data']['special_characters'] : ' !"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~',
      '#states' => [
        'visible' => [
          ':input[name="use_custom_special_characters"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::validateConfigurationForm($form, $form_state);

    if (empty($form_state->getValue('use_custom_special_characters'))) {
      $input = &$form_state->getUserInput();
      unset($input['special_characters']);
      $form_state->setValue('special_characters', NULL);
      $form_state->setValue('use_custom_special_characters', 0);
    }
    elseif (preg_match('/[a-z0-9]/i', $form_state->getValue('special_characters'))) {
      $form_state->setError($form['special_characters'], $this->t('Alphanumeric characters are not allowed.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    if ($form_state->getValue('use_custom_special_characters')) {
      $this->configuration['data']['use_custom_special_characters'] = TRUE;
      $this->configuration['data']['special_characters'] = $form_state->getValue('special_characters');
    }
    else {
      $this->configuration['data']['use_custom_special_characters'] = FALSE;
      unset($this->configuration['data']['special_characters']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary(): array {
    $summary = parent::getSummary();
    $summary['#items'][] = $this->t('Define special characters: %characters', ['%characters' => $this->configuration['data']['use_custom_special_characters'] ? $this->t('Yes') : $this->t('No')]);
    if ($this->configuration['data']['use_custom_special_characters']) {
      $summary['#items'][] = $this->t('Special characters: %characters', ['%characters' => $this->configuration['data']['special_characters']]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value): void {
    if ($this->configuration['data']['use_custom_special_characters']) {
      $special_characters = preg_quote($this->configuration['data']['special_characters'], '/');
      $result = !empty($special_characters) ? preg_replace("/([^{$special_characters}])/", '', $value) : '';
    }
    else {
      $result = preg_replace('/([a-z0-9])/i', '', $value);
    }

    try {
      parent::validate($result ?? '');
    }
    catch (PasswordConstraintPluginValidationException $e) {
      if ($this->configuration['data']['use_custom_special_characters']) {
        $count = $this->configuration['data']['minimum_characters'] - mb_strlen($result);
        $message = $count > 1 ? strtr($this->configuration['data']['descriptionPlural'], [
          '@minimum_characters' => $count,
          '@special_characters' => $this->configuration['data']['special_characters'],
        ]) : $this->configuration['data']['descriptionSingular'];
      }
      else {
        $message = $e->getMessage();
      }

      throw new PasswordConstraintPluginValidationException($message, $e->getCode(), $e);
    }
  }

}
