<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Form\ConfirmFormBase;
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
 * Form for deleting a password constraint.
 *
 * @internal
 */
final class PasswordConstraintDeleteForm extends ConfirmFormBase {

  /**
   * The user role containing the password constraint to be deleted.
   *
   * @var \Drupal\user\RoleInterface
   */
  private $role;

  /**
   * The password policy containing the password constraint to be deleted.
   *
   * @var \Drupal\password_enhancements\PasswordPolicyInterface
   */
  private $policy;

  /**
   * The password constraint to be deleted.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintInterface
   */
  private $constraint;

  /**
   * The password policy manager.
   *
   * @var \Drupal\password_enhancements\PasswordPolicyManagerService
   */
  private $policyManager;

  /**
   * The constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  private $constraintPluginManager;

  /**
   * PasswordConstraintDeleteForm constructor.
   *
   * @param \Drupal\password_enhancements\PasswordPolicyManagerService $policy_manager
   *   The password policy manager.
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $constraint_plugin_manager
   *   The constraint plugin manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(PasswordPolicyManagerService $policy_manager, PasswordConstraintPluginManager $constraint_plugin_manager, MessengerInterface $messenger, TranslationInterface $string_translation) {
    $this->policyManager = $policy_manager;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
    $this->constraintPluginManager = $constraint_plugin_manager;
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
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the %constraint constraint from the %role role?', [
      '%constraint' => $this->constraint->name(),
      '%role' => $this->role->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->role->toUrl('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_constraint_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RoleInterface $user_role = NULL, PasswordConstraintInterface $password_constraint = NULL) {
    $this->role = $user_role;
    $this->policy = PasswordPolicy::createFromRole($this->constraintPluginManager, $this->role);
    $this->constraint = $password_constraint;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->policyManager->deletePasswordConstraint($this->policy, $this->constraint);
    $this->messenger()->addStatus($this->t('The password constraint %name has been deleted.', ['%name' => $this->constraint->name()]));
    $form_state->setRedirectUrl($this->role->toUrl('edit-form'));
  }

}
