<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('password_enhancements_policy'),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#title'] = $this->t('Edit policy');

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->entity->getName(),
      '#required' => TRUE,
    ];

    $options = [];
    foreach ($this->roleStorage->loadMultiple() as $role) {
      $id = $role->id();
      if ($id == 'anonymous') {
        continue;
      }

      $options[$id] = $role->label();
    }

    $form['role'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('User role'),
      '#description' => $this->t('Select a role for which this policy should be applied.<br>When the user is registering only <em>All</em> and <em>Authenticated user</em> role policies will be used.'),
      '#default_value' => $this->entity->getRole(),
      '#required' => TRUE,
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['minimumRequiredConstraints'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum required constraints'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $this->entity->getMinimumRequiredConstraints() ?? 1,
    ];

    $expire_days = $this->entity->getExpireDays();
    $form['expire_password'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expire password'),
      '#default_value' => $expire_days > 0,
    ];

    $form['expireDays'] = [
      '#type' => 'number',
      '#title' => $this->t('Expire password after'),
      '#field_suffix' => $this->t('day(s).'),
      '#default_value' => $expire_days,
      '#min' => 0,
      '#states' => [
        'visible' => [
          ':input[name="expire_password"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['priority'] = [
      '#type' => 'number',
      '#title' => $this->t('Priority'),
      '#default_value' => $this->entity->getPriority() ?? 1,
      '#min' => 0,
      '#states' => [
        'invisible' => [
          ':input[name="role"]' => ['value' => 'authenticated'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($this->entity->isNew()) {
      $role = $form_state->getValue('role');
      if (!empty($this->policyConfigEntityStorage->load($role))) {
        $form_state->setError($form['role'], $this->t('A policy is already created for the %role role.', [
          '%role' => $this->roleStorage->load($role)->label(),
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('expire_password')) {
      $form_state->setValue('expireDays', 0);
    }

    if ($form_state->getValue('role') == 'authenticated') {
      $form_state->setValue('priority', 0);
    }

    parent::submitForm($form, $form_state);

    $form_state->setRedirect('entity.password_enhancements_policy.collection');
    $this->messenger->addStatus(t('The %name password policy has been successfully saved.', [
      '%name' => $form_state->getValue('name'),
    ]));
  }

}
