<?php

namespace Drupal\password_enhancements;

use Drupal\password_enhancements\Routing\RouteSubscriber;
use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds service if the config_translation is enabled.
 */
class PasswordEnhancementsExtensionPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Probe the module to check whether if it is active or not.
    try {
      $container->get('module_handler')->getModule('config_translation');
    }
    catch (Exception $e) {
      // If the module is not active, we don't have to do anything.
      return;
    }

    // Register route subscriber service for config_translation module.
    $definition = new Definition(RouteSubscriber::class, [
      new Reference('plugin.manager.config_translation.mapper'),
    ]);
    $definition->addTag('event_subscriber');
    $container->setDefinition('password_enhancements.config_translation.route_subscriber', $definition);
  }

}
