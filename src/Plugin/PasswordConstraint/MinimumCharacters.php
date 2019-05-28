<?php

namespace Drupal\password_enhancements\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\password_enhancements\Plugin\PasswordConstraintPluginInterface;

/**
 * Defines a common class for minimum character requirement.
 */
abstract class MinimumCharacters extends PluginBase implements PasswordConstraintPluginInterface {

  /**
   * The generated error message.
   *
   * @var string
   */
  protected $errorMessage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->errorMessage = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrorMessage(): ?string {
    return $this->errorMessage;
  }

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
      '#title' => t('Minimum characters'),
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
  public function settingsSubmit(array &$form, FormStateInterface $form_state): void {
    // Nothing to do here.
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $value, bool $custom_error_message = FALSE): bool {
    $character_count = mb_strlen($value);
    if ($this->configuration['minimum_characters'] <= $character_count) {
      $this->errorMessage = NULL;
      return TRUE;
    }

    if (!$custom_error_message) {
      $count = $this->configuration['minimum_characters'] - $character_count;
      $this->errorMessage = $this->formatPlural($count, $this->configuration['descriptionSingular'], $this->configuration['descriptionPlural'], [
        '@minimum_characters' => $count,
      ]);
    }
    return FALSE;
  }

}
