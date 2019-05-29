<?php

namespace Drupal\password_enhancements\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Registers a new event subscriber for navigation locking.
 */
class NavigationLock implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Constructs the Legal navigation lock event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session manager.
   */
  public function __construct(MessengerInterface $messenger, ModuleHandlerInterface $module_handler, SessionManagerInterface $session_manager) {
    $this->messenger = $messenger;
    $this->moduleHandler = $module_handler;
    $this->sessionManager = $session_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['onKernelRequestLockNavigation', 33];
    return $events;
  }

  /**
   * Locks the user to the password change form.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function onKernelRequestLockNavigation(GetResponseEvent $event): void {
    /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $attributes_bag */
    $attributes_bag = $this->sessionManager->getBag('attributes')->getBag();
    $password_change_required = $attributes_bag->get('password_enhancements_password_change_required');

    if ($password_change_required) {
      $allowed_paths = [
        '/user/password-change',
        '/user/logout',
      ];

      // Allow altering the allowed paths.
      $this->moduleHandler->alter('password_enhancements_allowed_paths', $allowed_paths);

      $request = $event->getRequest();
      $current_path = $request->getPathInfo();
      if (!in_array($current_path, $allowed_paths)) {
        $pass_reset_token = $event->getRequest()->query->get('pass-reset-token');
        $url_options = [];
        if ($pass_reset_token !== NULL || $attributes_bag->get('password_enhancements_pass_reset_token') !== NULL) {
          $url_options['query'] = [
            'pass-reset-token' => $pass_reset_token ?? $attributes_bag->get('password_enhancements_pass_reset_token'),
          ];
          $this->messenger->addError($this->t('You need to change your password before continuing.'));

          if ($pass_reset_token !== NULL) {
            $attributes_bag->set('password_enhancements_pass_reset_token', $pass_reset_token);
          }
        }
        else {
          $this->messenger->addError($this->t('Your password has expired and must be changed before continuing.'));
        }

        $response = new RedirectResponse(URL::fromRoute('password_enhancements.password_change', [], $url_options)->toString());
        $event->setResponse($response);
      }
    }
  }

}
