<?php

namespace Drupal\password_enhancements\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom user page builder.
 */
class UserLogoutController extends ControllerBase {

  const REASON_ADMIN_LOGOUT = 'admin_logout';

  /**
   * Shows a message for the user if the user was logged out.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A logout reason message or a redirect response if the reason not defined.
   */
  public function loggedOut(Request $request) {
    switch ($request->get('reason')) {
      case self::REASON_ADMIN_LOGOUT:
        return [
          '#message' => $this->t('You have been logged out because the administrator required you to change your password.<br>Please <a href="@login_url">login</a> again to change your password.', [
            '@login_url' => Url::fromRoute('user.login')->toString(),
          ]),
          '#theme' => 'password_enhancements_logged_out_message',
        ];

      default:
        // In any other case just redirect the user to the front page.
        return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }
  }

}
