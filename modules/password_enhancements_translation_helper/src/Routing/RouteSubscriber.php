<?php

namespace Drupal\password_enhancements_translation_helper\Routing;

use Drupal\config_translation\ConfigMapperManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamic route listener.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The mapper plugin discovery service.
   *
   * @var \Drupal\config_translation\ConfigMapperManagerInterface
   */
  protected $mapperManager;

  /**
   * Constructs a new RouteSubscriber.
   *
   * @param \Drupal\config_translation\ConfigMapperManagerInterface $mapper_manager
   *   The mapper plugin discovery service.
   */
  public function __construct(ConfigMapperManagerInterface $mapper_manager) {
    $this->mapperManager = $mapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Come after the config translation route subscriber.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -111];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $mappers = $this->mapperManager->getMappers($collection);
    $constraint = &$mappers['password_enhancements_constraint'];
    $route = $constraint->getOverviewRoute();
    $route->setOption('parameters', $route->getOption('parameters') + [
      'password_enhancements_policy' => [
        'type' => 'entity:password_enhancements_policy',
      ],
    ]);

    $collection->add($constraint->getOverviewRouteName(), $route);
  }

}
