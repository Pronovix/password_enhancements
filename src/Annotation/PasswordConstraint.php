<?php

namespace Drupal\password_enhancements\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a password constraint object.
 *
 * @Annotation
 */
class PasswordConstraint extends Plugin {

  /**
   * The ID of the constraint.
   *
   * @var string
   */
  public $id;

  /**
   * A human-readable name for the constraint.
   *
   * @var string
   */
  public $name;

  /**
   * Constraint's description.
   *
   * @var string
   */
  public $description;

  /**
   * Defines whether the plugin can be used only one-time or multiple times.
   *
   * @var bool
   */
  public $unique;

  /**
   * Library reference that validates the plugin on the front-end.
   *
   * The referenced library has to be defined in the mymodule.libraries.yml
   * file.
   * If your plugin doesn't define a front-end validation for the plugin, then
   * set it to NULL.
   *
   * @var string|null
   */
  public $jsLibrary;

}
