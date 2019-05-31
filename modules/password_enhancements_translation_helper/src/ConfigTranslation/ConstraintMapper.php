<?php

namespace Drupal\password_enhancements_translation_helper\ConfigTranslation;

use Drupal\config_translation\ConfigEntityMapper;

/**
 * Provides a configuration mapper for password constraint config entities.
 */
class ConstraintMapper extends ConfigEntityMapper {

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteParameters() {
    return [
      $this->entityType => $this->entity->id(),
      'password_enhancements_policy' => $this->entity->getPolicy(),
    ];
  }

}
