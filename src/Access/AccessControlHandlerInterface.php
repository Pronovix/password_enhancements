<?php

namespace Drupal\password_enhancements\Access;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines required methods for the access control handler.
 */
interface AccessControlHandlerInterface extends AccessInterface {

  /**
   * Check whether the user has access to the password change page or not.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function hasPasswordChangeAccess(AccountInterface $current_user): AccessResultInterface;

}
