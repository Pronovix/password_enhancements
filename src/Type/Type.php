<?php

namespace Drupal\password_enhancements\Type;

/**
 * Defines a base type class.
 */
abstract class Type {

  /**
   * The value of the type instance.
   *
   * @var mixed
   */
  private $value;

  /**
   * Constructs a new Type object.
   *
   * @param mixed $value
   *   The value of the current type.
   */
  protected function __construct($value) {
    $this->value = $value;
  }

  /**
   * String representation of the value.
   */
  public function __toString(): string {
    return (string) $this->value;
  }

  /**
   * Gets the actual value.
   *
   * @return mixed
   *   The non-type casted value of the current type.
   */
  public function getValue() {
    return $this->value;
  }

}
