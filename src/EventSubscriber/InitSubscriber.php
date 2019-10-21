<?php

namespace Drupal\password_enhancements\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\password_enhancements\PasswordChecker;
use Drupal\password_enhancements\PasswordPolicyManagerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to Drupal initialization event.
 */
final class InitSubscriber implements EventSubscriberInterface {

  /**
   * Account proxy.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $account;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * Password checker service.
   *
   * @var \Drupal\password_enhancements\PasswordChecker
   */
  private $passwordChecker;

  /**
   * User storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $userStorage;

  /**
   * The password policy manager.
   *
   * @var \Drupal\password_enhancements\PasswordPolicyManagerService
   */
  private $policyManager;

  /**
   * Initializes the init subscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\password_enhancements\PasswordChecker $password_checker
   *   Password checker service.
   * @param \Drupal\password_enhancements\PasswordPolicyManagerService $policy_manager
   *   The password policy manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(AccountProxyInterface $account, DateFormatterInterface $date_formatter, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, PasswordChecker $password_checker, PasswordPolicyManagerService $policy_manager) {
    $this->account = $account;
    $this->dateFormatter = $date_formatter;
    $this->messenger = $messenger;
    $this->passwordChecker = $password_checker;
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->policyManager = $policy_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // To check if the password change is required and redirect the user
    // properly we have to do it before the navigation lock event which weight
    // is 33.
    $events[KernelEvents::REQUEST][] = ['checkRequiredPasswordChange', 32];
    // The password change notification has to happen only after the navigation
    // lock event which weight is 33.
    $events[KernelEvents::REQUEST][] = ['passwordChangeNotification', 34];
    return $events;
  }

  /**
   * Checks if the user has to change the password already or not.
   *
   * Sets the password change required field to true to force password change if
   * the user's password expired.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   If the user could not be saved.
   */
  public function checkRequiredPasswordChange(GetResponseEvent $event): void {
    if ($event->isMasterRequest()) {
      $policy = $this->policyManager->loadPolicyByRoles($this->account->getRoles());
      if ($policy !== NULL) {
        /** @var \Drupal\user\UserInterface $user */
        $user = $this->userStorage->load($this->account->id());
        $is_password_change_required = $user->get('password_enhancements_password_change_required')->getValue() ? (bool) $user->get('password_enhancements_password_change_required')->getValue()[0]['value'] : FALSE;
        if (!$is_password_change_required && $this->passwordChecker->isExpired($policy)) {
          $user->set('password_enhancements_password_change_required', TRUE);
          $user->save();
        }
      }
    }
  }

  /**
   * Checks if the user's password is about to expire.
   *
   * If the user's password is about to expire it shows an error message if it
   * is enabled.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function passwordChangeNotification(GetResponseEvent $event): void {
    if ($event->isMasterRequest() && Url::fromRoute('password_enhancements.password_change')->toString() !== $event->getRequest()->getPathInfo()) {
      $policy = $this->policyManager->loadPolicyByRoles($this->account->getRoles());
      if ($policy !== NULL && $this->passwordChecker->isWarningMessageNeeded($policy)) {
        /** @var \Drupal\user\UserInterface $user */
        $user = $this->userStorage->load($this->account->id());
        $this->messenger->addWarning(new FormattableMarkup($policy->getExpiryWarningMessage(), [
          '@date_time' => $this->dateFormatter->format($user->get('password_enhancements_password_changed_date')->getValue()[0]['value'] + $policy->getExpireSeconds(), 'password_enhancements_date_format'),
          '@url' => $user->toUrl('edit-form')->toString(),
        ]));
      }
    }
  }

}
