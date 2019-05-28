<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form.
 */
class Settings extends ConfigFormBase {

  const CONSTRAINT_EFFECT_STRIKETHROUGH = 'strikethrough';
  const CONSTRAINT_EFFECT_HIDE = 'hide';

  /**
   * Default minimum password length.
   */
  const MINIMUM_LENGTH = 8;

  /**
   * Password constraint entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $passwordConstraintEntityStorage;

  /**
   * Constructs the settings from for password constraint.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $password_constraint_entity_storage
   *   Password constraint entity storage.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConfigEntityStorageInterface $password_constraint_entity_storage, MessengerInterface $messenger) {
    parent::__construct($config_factory);

    $this->passwordConstraintEntityStorage = $password_constraint_entity_storage;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('password_enhancements_constraint'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_enhancements_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'password_enhancements.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($this->config('user.settings')->get('password_strength')) {
      $this->messenger->addWarning(t('The core <a href="@url" target="_blank">password strength indicator</a> is enabled, it is recommended to disable it.', [
        '@url' => Url::fromRoute('entity.user.admin_form', [], [
          'fragment' => 'edit-user-password-strength',
        ])->toString(),
      ]));
    }

    $password_settings = $this->config('password_enhancements.settings');

    $form['constraint_update_effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Constraint update effect'),
      '#description' => t('Sets the update effect for password constraint completion.'),
      '#options' => [
        static::CONSTRAINT_EFFECT_STRIKETHROUGH => $this->t('Strike-through'),
        static::CONSTRAINT_EFFECT_HIDE => $this->t('Hide'),
      ],
      '#default_value' => $password_settings->get('constraint_update_effect') ?? static::CONSTRAINT_EFFECT_STRIKETHROUGH,
    ];

    $form['require_password'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Always require password at registration'),
      '#description' => $this->t('If enabled the password field will be always visible and required on the registration form.'),
      '#default_value' => $password_settings->get('require_password') ?? FALSE,
    ];

    $form['require_password_change'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require changing password for password reset'),
      '#description' => $this->t('If enabled then in case if the user is coming from a password reset URL then after logging in the user will be redirected to the password change page where it is mandatory to change the password.'),
      '#default_value' => $password_settings->get('require_password_change') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('password_enhancements.settings')
      ->set('constraint_update_effect', $form_state->getValue('constraint_update_effect'))
      ->set('require_password', $form_state->getValue('require_password'))
      ->set('require_password_change', $form_state->getValue('require_password_change'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
