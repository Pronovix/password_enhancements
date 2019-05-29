<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Password change form.
 */
class PasswordChange extends FormBase {

  /**
   * Date time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $user;

  /**
   * Password service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $password;

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Constructs the password change form.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   Date time.
   * @param \Drupal\user\UserInterface $user
   *   Current user.
   * @param \Drupal\Core\Password\PasswordInterface $password
   *   Password service.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session manager.
   */
  public function __construct(TimeInterface $date_time, UserInterface $user, PasswordInterface $password, SessionManagerInterface $session_manager) {
    $this->dateTime = $date_time;
    $this->user = $user;
    $this->password = $password;
    $this->sessionManager = $session_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): PasswordChange {
    $user_id = $container->get('current_user')->id();
    return new static(
      $container->get('datetime.time'),
      $container->get('entity_type.manager')->getStorage('user')->load($user_id),
      $container->get('password'),
      $container->get('session_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'password_enhancements_change_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Get user roles.
    $roles = $this->user->getRoles();
    $form_state->setValue('roles', $roles);
    $form_state->setUserInput(['roles' => $roles]);

    $user_pass_reset = FALSE;
    if (!$form_state->get('user_pass_reset') && ($token = $this->getRequest()->get('pass-reset-token'))) {
      $session_key = 'pass_reset_' . $this->user->id();
      $user_pass_reset = isset($_SESSION[$session_key]) && Crypt::hashEquals($_SESSION[$session_key], $token);
      $form_state->set('user_pass_reset', $user_pass_reset);
    }

    $form['account'] = [
      '#type' => 'container',
      'roles' => [
        '#type' => 'hidden',
        '#value' => $roles,
      ],
      'current_pass' => [
        '#type' => 'password',
        '#title' => $this->t('Current password'),
        '#required' => TRUE,
        '#attributes' => ['autocomplete' => 'off'],
        '#access' => !$user_pass_reset,
      ],
      'pass' => [
        '#type' => 'password_confirm',
        '#size' => 25,
        '#required' => TRUE,
      ],
    ];

    $form['actions'] = [
      '#type' => 'container',
      'save' => [
        '#type' => 'submit',
        '#value' => $this->t('Change'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    if (!$form_state->get('user_pass_reset') && !$this->password->check($form_state->getValue('current_pass'), $this->user->getPassword())) {
      $form_state->setError($form['account']['current_pass'], $this->t('Incorrect password.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Remove lock and clean-up session variables.
    $attributes_bag = $this->sessionManager->getBag('attributes')->getBag();
    $attributes_bag->remove('password_enhancements_password_change_required');
    $attributes_bag->remove('password_enhancements_pass_reset_token');

    // Update user fields.
    $this->user->set('password_change_required', FALSE)
      ->set('password_changed', $this->dateTime->getRequestTime())
      ->setPassword($form_state->getValue('pass'))
      ->save();

    $this->messenger()->addStatus($this->t('Your password has been successfully changed.'));
    $form_state->setRedirect('user.page');
  }

}
