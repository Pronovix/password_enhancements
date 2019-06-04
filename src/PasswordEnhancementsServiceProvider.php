<?php

namespace Drupal\password_enhancements;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Adds custom extension pass to provide services conditionally.
 */
class PasswordEnhancementsServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container->addCompilerPass(new PasswordEnhancementsExtensionPass());
  }

}
