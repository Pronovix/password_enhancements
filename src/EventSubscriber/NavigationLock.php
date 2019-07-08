<?php

namespace Drupal\password_enhancements\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Registers a new event subscriber for navigation locking.
 */
class NavigationLock implements EventSubscriberInterface {

  /**
   * Account proxy.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

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
   * Translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs the navigation lock event subscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   Account proxy.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session manager.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translation_manager
   *   Translation manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(AccountProxyInterface $account, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, ModuleHandlerInterface $module_handler, SessionManagerInterface $session_manager, TranslationManager $translation_manager) {
    $this->account = $account;
    $this->messenger = $messenger;
    $this->moduleHandler = $module_handler;
    $this->sessionManager = $session_manager;
    $this->translationManager = $translation_manager;
    $this->userStorage = $entity_type_manager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Navigation lock should happen after the RouteListener's onKernelRequest
    // which weight is 32.
    $events[KernelEvents::REQUEST][] = ['onKernelRequestLockNavigation', 33];
    return $events;
  }

  /**
   * Locks the user to the password change form if password change is required.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function onKernelRequestLockNavigation(GetResponseEvent $event): void {
    if ($event->isMasterRequest()) {
      /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $attributes_bag */
      $attributes_bag = $this->sessionManager->getBag('attributes')->getBag();
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->userStorage->load($this->account->id());

      if ($user->get('password_enhancements_password_change_required')->getValue()[0]['value']) {
        $allowed_paths = [
          Url::fromRoute('password_enhancements.password_change')->toString(),
          Url::fromRoute('user.logout')->toString(),
        ];

        // Allow altering the allowed paths.
        $this->moduleHandler->alter('password_enhancements_allowed_paths', $allowed_paths);

        $current_path = $event->getRequest()->getPathInfo();
        if (!in_array($current_path, $allowed_paths)) {
          $pass_reset_token = $event->getRequest()->query->get('pass-reset-token');
          $url_options = [];
          if ($pass_reset_token !== NULL || $attributes_bag->get('password_enhancements_pass_reset_token') !== NULL) {
            $url_options['query'] = [
              'pass-reset-token' => $pass_reset_token ?? $attributes_bag->get('password_enhancements_pass_reset_token'),
            ];
            $this->messenger->addError($this->translationManager->translate('You need to change your password before continuing.'));

            if ($pass_reset_token !== NULL) {
              $attributes_bag->set('password_enhancements_pass_reset_token', $pass_reset_token);
            }
          }
          else {
            $this->messenger->addError($this->translationManager->translate('Your password has expired and must be changed before continuing.'));
          }

          $response = new RedirectResponse(URL::fromRoute('password_enhancements.password_change', [], $url_options)->toString());
          $event->setResponse($response);
        }
      }
    }
  }

}
