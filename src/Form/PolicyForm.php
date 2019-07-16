<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\password_enhancements\Entity\Policy;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Password policy config entity form.
 */
class PolicyForm extends EntityForm {

  /**
   * Config entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $policyConfigEntityStorage;

  /**
   * Role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * Constructs the password policy config entity form.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $policy_config_entity_storage
   *   Password policy config entity storage.
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   Role storage.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   */
  public function __construct(ConfigEntityStorageInterface $policy_config_entity_storage, RoleStorageInterface $role_storage, MessengerInterface $messenger) {
    $this->policyConfigEntityStorage = $policy_config_entity_storage;
    $this->roleStorage = $role_storage;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): PolicyForm {
    return new static(
      $container->get('entity_type.manager')->getStorage('password_enhancements_policy'),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    // Load roles and store the loaded roles in the form storage.
    $roles = $this->roleStorage->loadMultiple();
    $form_state->setStorage(['roles' => $roles]);

    /** @var \Drupal\password_enhancements\Entity\PolicyInterface $entity */
    $entity = $this->entity;

    // Change default title.
    $form['#title'] = $this->t('Edit policy');

    $options = [];
    $current_role = $entity->getRole();
    $existing_policies = $this->policyConfigEntityStorage->loadMultiple();
    foreach ($roles as $role) {
      $id = $role->id();
      if ($id === AccountInterface::ANONYMOUS_ROLE || (array_key_exists($id, $existing_policies) && $current_role !== $id)) {
        continue;
      }
      $options[$id] = $role->label();
    }

    $form['role'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('User role'),
      '#description' => $this->t('Select a role for which this policy should be applied.<br>When the user is registering only the <em>Authenticated user</em> role policy will be used.'),
      '#default_value' => $current_role,
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    ];

    $form['minimumRequiredConstraints'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum required constraints'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $entity->getMinimumRequiredConstraints(),
    ];

    $expire_days = $entity->getExpireDays();
    $form['expire_password'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expire password'),
      '#default_value' => $expire_days > 0,
    ];

    $form['expire_days'] = [
      '#type' => 'number',
      '#title' => $this->t('Expire password after'),
      '#field_suffix' => $this->t('day(s).'),
      '#default_value' => $expire_days ?: 1,
      '#min' => 1,
      '#states' => [
        'visible' => [
          ':input[name="expire_password"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['expire_warn_before_days'] = [
      '#type' => 'number',
      '#title' => $this->t('Show warning'),
      '#description' => $this->t('Show a warning message before the password would expire.<br>If set to 0 it will not show any message.'),
      '#field_suffix' => $this->t('day(s) before the password expires.'),
      '#default_value' => $entity->getExpireWarnDays(),
      '#min' => 0,
      '#states' => [
        'visible' => [
          ':input[name="expire_password"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['expiryWarningMessage'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Expiry warning message'),
      '#description' => $this->t("This message will be presented for the user if the user's password is about to expire.<br>Available placeholders: <ul><li>@url: URL for the user edit page</li><li>@date_time: the date and time when the password will expire</li></ul>."),
      '#default_value' => $entity->getExpiryWarningMessage() ?? 'Your password will expire on @date_time, please <a href="@url">change your password</a> before it expires to prevent any potential data loss.',
      '#states' => [
        'invisible' => [
          ':input[name="expire_warn_before_days"]' => ['value' => 0],
        ],
      ],
    ];

    $form['priority'] = [
      '#type' => 'number',
      '#title' => $this->t('Priority'),
      '#default_value' => $entity->getPriority(),
      '#min' => 0,
      '#states' => [
        'invisible' => [
          ':input[name="role"]' => ['value' => AccountInterface::AUTHENTICATED_ROLE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    if ($form_state->getValue('expire_warn_before_days') !== Policy::PASSWORD_NO_WARNING && empty($form_state->getValue('expiryWarningMessage'))) {
      $form_state->setError($form['expiryWarningMessage'], $this->t('Expiry message cannot be empty if it is set to been shown.'));
    }

    if ($this->entity->isNew()) {
      $role = $form_state->getValue('role');
      if (!empty($this->policyConfigEntityStorage->load($role))) {
        $form_state->setError($form['role'], $this->t('A policy is already created for the %role role.', [
          '%role' => $form_state->getStorage()['roles'][$role]->label(),
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (!$form_state->getValue('expire_password')) {
      $form_state->setValue('expireSeconds', 0);
      $form_state->setValue('expireWarnSeconds', 0);
      $form_state->setValue('expiryWarningMessage', NULL);
    }
    else {
      // Store days in seconds.
      $form_state->setValue('expireSeconds', $form_state->getValue('expire_days') * 86400);
      $form_state->setValue('expireWarnSeconds', $form_state->getValue('expire_warn_before_days') * 86400);
    }

    if ($form_state->getValue('role') === AccountInterface::AUTHENTICATED_ROLE) {
      $form_state->setValue('priority', 0);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $translation_arguments = [
      '%role' => $this->roleStorage->load($form_state->getValue('role'))->label(),
    ];
    if ($status === SAVED_NEW) {
      $this->messenger->addStatus($this->t('Policy for the %role role has been successfully created.', $translation_arguments));
    }
    else {
      $this->messenger->addStatus($this->t('Policy for the %role role has been successfully updated.', $translation_arguments));
    }

    $form_state->setRedirect($this->entity->toUrl('collection')->getRouteName());

    return $status;
  }

}
