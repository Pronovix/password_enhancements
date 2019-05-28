<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Password constraint config entity form.
 */
class ConstraintForm extends EntityForm {

  /**
   * Config entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $constraintConfigEntityStorage;

  /**
   * Password constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $passwordConstraintPluginManager;

  /**
   * Constructs the entity form for password_enhancements_constraint entity.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $constraint_config_entity_storage
   *   Config entity storage.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $password_constraint_plugin_manager
   *   Password constraint plugin manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   */
  public function __construct(ConfigEntityStorageInterface $constraint_config_entity_storage, PasswordConstraintPluginManager $password_constraint_plugin_manager, MessengerInterface $messenger) {
    $this->constraintConfigEntityStorage = $constraint_config_entity_storage;
    $this->passwordConstraintPluginManager = $password_constraint_plugin_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('password_enhancements_constraint'),
      $container->get('plugin.manager.password_constraint'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#title'] = $this->t('Edit constraint');

    $options = [];
    foreach ($this->passwordConstraintPluginManager->getDefinitions() as $definition) {
      $options[$definition['id']] = $definition['name'];
    }

    $form['policy'] = [
      '#type' => 'hidden',
      '#value' => $this->getRequest()->get('password_enhancements_policy'),
    ];

    $settings_wrapper_id = 'ajax-wrapper';
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Constraint'),
      '#description' => $this->t('The type of the constraint.'),
      '#options' => $options,
      '#default_value' => $this->entity->getType(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateForm',
        'event' => 'change',
        'wrapper' => $settings_wrapper_id,
      ],
      '#disabled' => !$this->entity->isNew(),
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
      $form['wrapper'] = static::updateForm($form, $form_state);
    }
    catch (PluginException $e) {
      $this->messenger->addError(t('Invalid plugin ID: @error', [
        '@error' => $e->getMessage(),
      ]));
      watchdog_exception('password_enhancements', $e);
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
  public function updateForm(array $form, FormStateInterface $form_state) {
    // Get type if exist.
    if (($type = $this->entity->getType()) === NULL) {
      $type = $form_state->getValue('type');
    }

    if ($type !== NULL) {
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $type = $form_state->getValue('type');

    if ($this->entity->isNew()) {
      // If the constraint marked as unique, make sure that only one can be
      // created for a given policy.
      $plugin_definition = $this->passwordConstraintPluginManager->getDefinition($type);
      if (!empty($plugin_definition['unique'])) {
        $entities = $this->constraintConfigEntityStorage->loadByProperties([
          'type' => $type,
        ]);
        if (array_key_exists($type . '.' . $this->getRequest()->get('password_enhancements_policy'), $entities)) {
          $form_state->setError($form['type'], t('Only one instance can be created from the selected constraint.'));
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Call plugin's submit callback.
    if (!array_key_exists('empty', $form['wrapper']['settings'])) {
      $plugin_instance = $this->passwordConstraintPluginManager->createInstance($form_state->getValue('type'), $this->entity->getConfiguration());
      $plugin_instance->settingsSubmit($form['wrapper']['settings'], $form_state);
    }

    parent::submitForm($form, $form_state);

    $this->messenger->addStatus('The password constraint was successfully saved.');
    $form_state->setRedirect('entity.password_enhancements_constraint.collection', [
      'password_enhancements_policy' => $this->getRequest()->get('password_enhancements_policy'),
    ]);
  }

}
