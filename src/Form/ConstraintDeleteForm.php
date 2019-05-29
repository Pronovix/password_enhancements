<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Builds delete form for the password policy config entity.
 */
class ConstraintDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to delete the %type constraint?', [
      '%type' => $this->entity->getType(),
    ]);
  }

}
