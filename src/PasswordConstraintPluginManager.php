<?php

namespace Drupal\password_enhancements;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\password_enhancements\Annotation\PasswordConstraint;

/**
 * Manages password constraint plugins.
 *
 * @see hook_password_enhancements_constraint_info_alter()
 * @see \Drupal\password_enhancements\Annotation\PasswordConstraint
 * @see \Drupal\password_enhancements\PasswordConstraintInterface
 * @see \Drupal\password_enhancements\Plugin\PasswordConstraint\PasswordConstraintBase
 * @see plugin_api
 */
final class PasswordConstraintPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new PasswordConstraintPluginManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/PasswordConstraint',
      $namespaces,
      $module_handler,
      PasswordConstraintInterface::class,
      PasswordConstraint::class
    );

    $this->alterInfo('password_enhancements_constraint_info');
    $this->setCacheBackend($cache_backend, 'password_enhancements_constraint_plugins');
  }

}
