<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\password_enhancements\PasswordConstraintInterface;
use Drupal\user\RoleInterface;

/**
 * Provides an add form for password constraints.
 *
 * @internal
 */
final class PasswordConstraintAddForm extends PasswordConstraintFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_constraint_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RoleInterface $user_role = NULL, $password_constraint = NULL): array {
    $form = parent::buildForm($form, $form_state, $user_role, $password_constraint);

    $form['#title'] = $this->t('Add %name constraint', ['%name' => $this->constraint->name()]);
    $form['actions']['submit']['#value'] = $this->t('Add constraint');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePasswordConstraint($password_constraint_type): PasswordConstraintInterface {
    return $this->constraintPluginManager->createInstance($password_constraint_type['id']);
  }

}
