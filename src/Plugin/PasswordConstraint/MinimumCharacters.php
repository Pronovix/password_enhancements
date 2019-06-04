<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\password_enhancements\Plugin\Exception\PasswordConstraintPluginValidationException;
use Drupal\password_enhancements\Plugin\PasswordConstraintPluginInterface;

/**
 * Defines a common class for minimum character requirement.
 */
abstract class MinimumCharacters extends PluginBase implements PasswordConstraintPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getInitialDescription(): string {
    if ($this->configuration['minimum_characters'] > 1) {
      return $this->configuration['descriptionPlural'];
    }
    else {
      return $this->configuration['descriptionSingular'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(): array {
    $form = [];

    $form['minimum_characters'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum characters'),
      '#required' => TRUE,
      '#min' => 1,
      '#default_value' => !empty($this->configuration['minimum_characters']) ? $this->configuration['minimum_characters'] : 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValidate(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getValue(['settings', 'minimum_characters']) < 1) {
      $form_state->setError($form['settings']['minimum_characters'], $this->t('The %title field must be a non-zero, positive number.', [
        '%title' => $form['settings']['minimum_characters']['#title'],
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value): void {
    $character_count = mb_strlen($value);
    if ($this->configuration['minimum_characters'] > $character_count) {
      $count = $this->configuration['minimum_characters'] - $character_count;
      $message = $count > 1 ? strtr($this->configuration['descriptionPlural'], [
        '@minimum_characters' => $count,
      ]) : $this->configuration['descriptionSingular'];
      throw new PasswordConstraintPluginValidationException($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSubmit(array &$form, FormStateInterface $form_state): void {
    // Nothing to do here.
  }

}
