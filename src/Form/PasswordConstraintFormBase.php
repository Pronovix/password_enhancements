<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\password_enhancements\PasswordConstraintInterface;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Drupal\password_enhancements\PasswordPolicy;
use Drupal\password_enhancements\PasswordPolicyManagerService;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base form for password constraints.
 */
abstract class PasswordConstraintFormBase extends FormBase {

  /**
   * The password policy.
   *
   * @var \Drupal\password_enhancements\PasswordPolicyInterface
   */
  protected $policy;

  /**
   * The password constraint.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintInterface
   */
  protected $constraint;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The password policy manager.
   *
   * @var \Drupal\password_enhancements\PasswordPolicyManagerService
   */
  protected $policyManager;

  /**
   * The constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $constraintPluginManager;

  /**
   * PasswordConstraintFormBase constructor.
   *
   * @param \Drupal\password_enhancements\PasswordPolicyManagerService $policy_manager
   *   The password policy manager.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $constraint_plugin_plugin_manager
   *   The constraint plugin manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(PasswordPolicyManagerService $policy_manager, PasswordConstraintPluginManager $constraint_plugin_plugin_manager, MessengerInterface $messenger, TranslationInterface $string_translation) {
    $this->policyManager = $policy_manager;
    $this->constraintPluginManager = $constraint_plugin_plugin_manager;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('password_enhancements.password_policy_manager'),
      $container->get('plugin.manager.password_enhancements.constraint'),
      $container->get('messenger'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RoleInterface $user_role = NULL, $password_constraint = NULL) {
    // Bail out early if the role doesn't have a policy at all.
    $this->policy = PasswordPolicy::createFromRole($this->constraintPluginManager, $user_role);
    // No need to validate the input, as the paramconverter already did that.
    $this->constraint = $this->preparePasswordConstraint($password_constraint);
    $form['uuid'] = [
      '#type' => 'value',
      '#value' => $this->constraint->getUuid(),
    ];
    $form['id'] = [
      '#type' => 'value',
      '#value' => $this->constraint->getPluginId(),
    ];

    $form = $this->constraint->buildConfigurationForm($form, $form_state);

    // Check the URL for the password constraint, otherwise use default.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $user_role->toUrl('edit-form'),
      '#attributes' => ['class' => ['button']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->constraint->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    $this->constraint->submitConfigurationForm($form, $form_state);

    if (!$this->constraint->getUuid()) {
      $this->policyManager->addPasswordConstraint($this->policy, $this->constraint->getConfiguration());
    }
    $this->policyManager->savePolicy($this->policy);

    $this->messenger()->addStatus($this->t('The password policy was successfully applied.'));
    $form_state->setRedirectUrl($this->policy->getRole()->toUrl('edit-form'));
  }

  /**
   * Converts a password constraint ID into an object.
   *
   * @param string|\Drupal\password_enhancements\PasswordConstraintInterface $password_constraint
   *   The password constraint (or its plugin ID during creation).
   *
   * @return \Drupal\password_enhancements\PasswordConstraintInterface
   *   The password constraint object.
   */
  abstract protected function preparePasswordConstraint($password_constraint): PasswordConstraintInterface;

}
