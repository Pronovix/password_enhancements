<?php

namespace Drupal\password_enhancements\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\password_enhancements\Controller\UserLogoutController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to Drupal initialization event.
 */
class Init implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * The current user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * Initializes the init subscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(AccountProxyInterface $account, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, SessionManagerInterface $session_manager) {
    $this->messenger = $messenger;
    $this->sessionManager = $session_manager;
    $this->user = $entity_type_manager->getStorage('user')->load($account->id());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['userForceLogout', 100];
    return $events;
  }

  /**
   * Forces the user to logout if the password_change_required field was set.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function userForceLogout(GetResponseEvent $event): void {
    // This session variable is set only if the user just logged in, in that
    // case don't force the user logout, but let changing the password.
    $password_change_required = $this->sessionManager->getBag('attributes')->getBag()->get('password_enhancements_password_change_required');

    if (!empty($this->user->password_change_required->value) && empty($password_change_required)) {
      // After logging the user out, redirect the user to a page where the
      // reason is explained, it is not possible to set message while logging
      // the user out because the session that stores the message are being
      // destroyed.
      user_logout();
      $response = new RedirectResponse(Url::fromRoute('password_enhancements.user.logged_out', [], [
        'query' => [
          'reason' => UserLogoutController::REASON_ADMIN_LOGOUT,
        ],
      ])->toString());
      $event->setResponse($response);
    }
  }

}
