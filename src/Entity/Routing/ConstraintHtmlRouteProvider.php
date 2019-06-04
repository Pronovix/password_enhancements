<?php

namespace Drupal\password_enhancements\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * HTML route provider for password constraint entities.
 */
class ConstraintHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getAddFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getAddFormRoute($entity_type);
    $this->addPolicyOption($route);
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionRoute(EntityTypeInterface $entity_type) {
    $route = parent::getCollectionRoute($entity_type);
    $this->addPolicyOption($route);
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeleteFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getDeleteFormRoute($entity_type);
    $this->addPolicyOption($route);
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getEditFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getEditFormRoute($entity_type);
    $this->addPolicyOption($route);
    return $route;
  }

  /**
   * Adds password policy option to the given route.
   *
   * @param \Symfony\Component\Routing\Route|null $route
   *   Route object.
   */
  protected function addPolicyOption(?Route $route) {
    if (!empty($route)) {
      $route->setOption('parameters', array_merge_recursive($route->getOption('parameters') ?? [], [
        'password_enhancements_policy' => [
          'type' => 'entity:password_enhancements_policy',
        ],
      ]));
    }
  }

}
