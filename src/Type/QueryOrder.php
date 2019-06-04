<?php

namespace Drupal\password_enhancements\Type;

/**
 * Defines a query order type.
 */
final class QueryOrder extends Type {

  /**
   * Ascending order.
   *
   * @return \Drupal\password_enhancements\Type\QueryOrder
   *   Ascending query order representation object.
   */
  public static function asc(): QueryOrder {
    return new static('asc');
  }

  /**
   * Descending order.
   *
   * @return \Drupal\password_enhancements\Type\QueryOrder
   *   Descending query order representation object.
   */
  public static function desc(): QueryOrder {
    return new static('desc');
  }

}
