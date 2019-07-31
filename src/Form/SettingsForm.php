<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constraint completion strikethrough effect.
   */
  const CONSTRAINT_EFFECT_STRIKETHROUGH = 'strikethrough';

  /**
   * Constraint completion hide effect.
   */
  const CONSTRAINT_EFFECT_HIDE = 'hide';

  /**
   * Default minimum password length.
   */
  const MINIMUM_LENGTH = 8;

  /**
   * Constructs the settings form for a password constraint.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger) {
    parent::__construct($config_factory);

    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): SettingsForm {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'password_enhancements_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'password_enhancements.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    if ($this->config('user.settings')->get('password_strength')) {
      $this->messenger->addWarning($this->t('The built-in <a href=":url" target="_blank">password strength indicator</a> is enabled, it is recommended to disable it.', [
        ':url' => Url::fromRoute('entity.user.admin_form', [], [
          'fragment' => 'edit-user-password-strength',
        ])->toString(),
      ]));
    }

    $password_settings = $this->config('password_enhancements.settings');

    $form['constraint_update_effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Constraint update effect'),
      '#description' => $this->t('Sets the update effect for password constraint completion.'),
      '#options' => [
        static::CONSTRAINT_EFFECT_STRIKETHROUGH => $this->t('Strike-through'),
        static::CONSTRAINT_EFFECT_HIDE => $this->t('Hide'),
      ],
      '#default_value' => $password_settings->get('constraint_update_effect'),
    ];

    $form['require_password'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Always require password at registration'),
      '#description' => $this->t('If enabled the password field will be always visible and required on the registration form.'),
      '#default_value' => $password_settings->get('require_password'),
    ];

    $form['require_password_change'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require changing password for password reset'),
      '#description' => $this->t('If enabled then in case if the user is coming from a password reset URL then after logging in the user will be redirected to the password change page where it is mandatory to change the password.'),
      '#default_value' => $password_settings->get('require_password_change'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('password_enhancements.settings')
      ->set('constraint_update_effect', $form_state->getValue('constraint_update_effect'))
      ->set('require_password', $form_state->getValue('require_password'))
      ->set('require_password_change', $form_state->getValue('require_password_change'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
