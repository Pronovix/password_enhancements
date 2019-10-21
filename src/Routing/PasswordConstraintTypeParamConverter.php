<?php

namespace Drupal\password_enhancements\Routing;

use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Converts parameters for upcasting password constraint names to full arrays.
 *
 * @internal
 */
final class PasswordConstraintTypeParamConverter implements ParamConverterInterface {

  /**
   * The constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  private $constraintPluginManager;

  /**
   * Constructs a new PasswordConstraintTypeParamConverter.
   *
   * @param \Drupal\password_enhancements\PasswordConstraintPluginManager $constraint_plugin_manager
   *   The constraint plugin manager.
   */
  public function __construct(PasswordConstraintPluginManager $constraint_plugin_manager) {
    $this->constraintPluginManager = $constraint_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults): array {
    return $this->constraintPluginManager->getDefinition($value, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route): bool {
    return (!empty($definition['type']) && $definition['type'] === 'password_constraint_type');
  }

}
