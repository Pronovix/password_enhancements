<?php

namespace Drupal\password_enhancements\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\password_enhancements\PasswordChecker;
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
  protected $account;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Password checker service.
   *
   * @var \Drupal\password_enhancements\PasswordChecker
   */
  protected $passwordChecker;

  /**
   * Policy storage.
   *
   * @var \Drupal\password_enhancements\Entity\Storage\PolicyEntityStorageInterface
   */
  protected $policyStorage;

  /**
   * User storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(AccountProxyInterface $account, DateFormatterInterface $date_formatter, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, PasswordChecker $password_checker) {
    $this->account = $account;
    $this->dateFormatter = $date_formatter;
    $this->messenger = $messenger;
    $this->passwordChecker = $password_checker;
    $this->policyStorage = $entity_type_manager->getStorage('password_enhancements_policy');
    $this->userStorage = $entity_type_manager->getStorage('user');
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
  public function checkRequiredPasswordChange(GetResponseEvent $event) {
    if ($event->isMasterRequest()) {
      $policy = $this->policyStorage->loadByRoleAndPriority($this->account->getRoles());
      if ($policy !== NULL) {
        /** @var \Drupal\user\UserInterface $user */
        $user = $this->userStorage->load($this->account->id());
        if (!$user->get('password_enhancements_password_change_required')->getValue()[0]['value'] && $this->passwordChecker->isExpired($policy)) {
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
  public function passwordChangeNotification(GetResponseEvent $event) {
    if ($event->isMasterRequest() && Url::fromRoute('password_enhancements.password_change')->toString() !== $event->getRequest()->getPathInfo()) {
      $policy = $this->policyStorage->loadByRoleAndPriority($this->account->getRoles());
      if ($policy !== NULL && $this->passwordChecker->showWarningMessage($policy)) {
        /** @var \Drupal\user\UserInterface $user */
        $user = $this->userStorage->load($this->account->id());
        $this->messenger->addWarning(new FormattableMarkup($policy->getExpiryWarningMessage(), [
          '@date_time' => $this->dateFormatter->format($user->get('password_enhancements_password_changed_date')->getValue()[0]['value'] + $policy->getExpireSeconds(), 'password_enhancements_date_format'),
          '@url' => Url::fromRoute('entity.user.edit_form', ['user' => $user->id()])->toString(),
        ]));
      }
    }
  }

}
