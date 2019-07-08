<?php

namespace Drupal\password_enhancements\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\password_enhancements\Access\AccessControlHandler;

/**
 * HTML route provider for password policy entities.
 */
class PolicyHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getAddFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getAddFormRoute($entity_type);
    $route->addRequirements([
      '_custom_access' => AccessControlHandler::class . '::canCreatePolicy',
    ]);
    return $route;
  }

}
