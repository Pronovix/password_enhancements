<?php

namespace Drupal\password_enhancements;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\password_enhancements\Annotation\PasswordConstraint;
use Drupal\password_enhancements\Plugin\PasswordConstraintPluginInterface;

/**
 * Password constraint plugin manager.
 */
class PasswordConstraintPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/PasswordConstraint',
      $namespaces,
      $module_handler,
      PasswordConstraintPluginInterface::class,
      PasswordConstraint::class
    );

    $this->alterInfo('password_enhancements_constraint_info');
    $this->setCacheBackend($cache_backend, 'password_enhancements_constraint_plugins');
  }

}
