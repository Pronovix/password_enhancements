<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\password_enhancements\Entity\Storage\ConstraintEntityStorageInterface;
use Drupal\password_enhancements\Logger\Logger;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Password constraint config entity form.
 */
class ConstraintForm extends EntityForm {

  /**
   * Config entity storage.
   *
   * @var \Drupal\password_enhancements\Entity\Storage\ConstraintEntityStorageInterface
   */
  protected $constraintConfigEntityStorage;

  /**
   * Logger channel.
   *
   * @var \Drupal\password_enhancements\Logger\Logger
   */
  protected $logger;

  /**
   * Password constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $passwordConstraintPluginManager;

  /**
   * Constructs the entity form for password_enhancements_constraint entity.
   *
   * @param \Drupal\password_enhancements\Entity\Storage\ConstraintEntityStorageInterface $constraint_config_entity_storage
   *   Config entity storage.
   * @param \Drupal\password_enhancements\Logger\Logger $logger
   *   Logger channel.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $password_constraint_plugin_manager
   *   Password constraint plugin manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(ConstraintEntityStorageInterface $constraint_config_entity_storage, Logger $logger, MessengerInterface $messenger, PasswordConstraintPluginManager $password_constraint_plugin_manager, RequestStack $request_stack) {
    $this->constraintConfigEntityStorage = $constraint_config_entity_storage;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->passwordConstraintPluginManager = $password_constraint_plugin_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ConstraintForm {
    return new static(
      $container->get('entity_type.manager')->getStorage('password_enhancements_constraint'),
      $container->get('logger.password_enhancements'),
      $container->get('messenger'),
      $container->get('plugin.manager.password_constraint'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    $form['#title'] = $this->t('Edit constraint');

    $options = [];
    $policy_id = $this->getRequest()->get('password_enhancements_policy')->id();
    $constraints = $this->constraintConfigEntityStorage->loadByRole($policy_id);
    $default_type = $this->entity->getType();
    foreach ($this->passwordConstraintPluginManager->getDefinitions() as $definition) {
      // Skip already added unique constraints.
      if (!empty($definition['unique']) && $default_type !== $definition['id'] && array_key_exists("{$policy_id}.{$definition['id']}", $constraints)) {
        continue;
      }

      $options[$definition['id']] = $definition['name'];
    }

    $form['policy'] = [
      '#type' => 'hidden',
      '#value' => $this->getRequest()->get('password_enhancements_policy')->id(),
    ];

    $settings_wrapper_id = 'ajax-wrapper';
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Constraint'),
      '#description' => $this->t('The type of the constraint.'),
      '#options' => $options,
      '#default_value' => $default_type,
      '#empty_value' => '',
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateForm',
        'event' => 'change',
        'wrapper' => $settings_wrapper_id,
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required'),
      '#description' => $this->t('If the constraint is required then it cannot be marked as optional if the minimum required constraints set by the policy are passed the validation.'),
      '#default_value' => $this->entity->isRequired(),
    ];

    $form['wrapper'] = [
      '#type' => 'container',
      '#prefix' => "<div id='{$settings_wrapper_id}'>",
      '#suffix' => '</div>',
      'descriptionSingular' => [
        '#type' => 'textfield',
        '#title' => $this->t('Description (singular)'),
        '#description' => $this->t('The singular description that should be displayed for the password field.'),
        '#required' => TRUE,
      ],
      'descriptionPlural' => [
        '#type' => 'textfield',
        '#title' => $this->t('Description (plural)'),
        '#description' => $this->t('The plural description that should be displayed for the password field.'),
        '#required' => TRUE,
      ],
      'settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Constraint settings'),
        '#tree' => TRUE,
      ],
    ];

    try {
      $form['wrapper'] = $this->updateForm($form, $form_state);
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Invalid plugin ID: @error', [
        '@error' => $e->getMessage(),
      ]));
      $this->logger->logException('Invalid plugin ID given.', $e);
    }

    return $form;
  }

  /**
   * Updates settings from.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The from's current state.
   *
   * @return array
   *   Renderable array for the settings wrapper.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function updateForm(array $form, FormStateInterface $form_state): array {
    // Get type if exist.
    if (($type = $this->entity->getType()) === '') {
      $type = $form_state->getValue('type', '');
    }

    if ($type !== '') {
      // Get plugin instance for the selected type.
      $plugin_instance = $this->passwordConstraintPluginManager->createInstance($type, $this->entity->getConfiguration());

      // Update description.
      if ($this->entity->isNew()) {
        // Unset description from the form state if the type was changed.
        $input = &$form_state->getUserInput();
        if (array_key_exists('descriptionSingular', $input)) {
          unset($input['descriptionSingular']);
        }

        if (array_key_exists('descriptionPlural', $input)) {
          unset($input['descriptionPlural']);
        }

        $form['wrapper']['descriptionSingular']['#default_value'] = $plugin_instance->defaultDescriptionSingular();
        $form['wrapper']['descriptionPlural']['#default_value'] = $plugin_instance->defaultDescriptionPlural();
      }
      else {
        $form['wrapper']['descriptionSingular']['#default_value'] = $this->entity->getDescriptionSingular();
        $form['wrapper']['descriptionPlural']['#default_value'] = $this->entity->getDescriptionPlural();
      }

      // Get plugin specific form elements if type is set.
      $form['wrapper']['settings'] += $plugin_instance->settingsForm();
    }
    else {
      // Show an empty message if there is no type selected yet.
      $form['wrapper']['settings']['empty'] = [
        '#type' => 'item',
        '#markup' => $this->t('Please select a constraint.'),
      ];
    }

    return $form['wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $type = $form_state->getValue('type');

    if ($this->entity->isNew()) {
      // If the constraint marked as unique, make sure that only one can be
      // created for a given policy.
      $plugin_definition = $this->passwordConstraintPluginManager->getDefinition($type);
      if (!empty($plugin_definition['unique'])) {
        $entities = $this->constraintConfigEntityStorage->loadByProperties([
          'type' => $type,
        ]);
        if (array_key_exists($this->getRequest()->get('password_enhancements_policy')->id() . '.' . $type, $entities)) {
          $form_state->setError($form['type'], $this->t('Only one instance can be created from the selected constraint.'));
        }
      }
    }

    // Validate plugin specific form elements.
    if (!array_key_exists('empty', $form['wrapper']['settings'])) {
      $plugin_instance = $this->passwordConstraintPluginManager->createInstance($type, $this->entity->getConfiguration());
      $plugin_instance->settingsValidate($form['wrapper']['settings'], $form_state);
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Call plugin's submit callback.
    if (!array_key_exists('empty', $form['wrapper']['settings'])) {
      $plugin_instance = $this->passwordConstraintPluginManager->createInstance($form_state->getValue('type'), $this->entity->getConfiguration());
      $plugin_instance->settingsSubmit($form['wrapper']['settings'], $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    if ($status === SAVED_NEW) {
      $this->messenger->addStatus('The constraint was successfully created.');
    }
    else {
      $this->messenger->addStatus('The constraint was successfully updated.');
    }

    $form_state->setRedirect($this->entity->toUrl('collection')->getRouteName(), [
      'password_enhancements_policy' => $this->entity->getPolicy(),
    ]);

    return $status;
  }

}
