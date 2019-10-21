<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\RoleInterface;
use Drupal\password_enhancements\PasswordConstraintInterface;

/**
 * Provides an edit form for password constraints.
 *
 * @internal
 */
final class PasswordConstraintEditForm extends PasswordConstraintFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_constraint_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RoleInterface $user_role = NULL, $password_constraint = NULL): array {
    $form = parent::buildForm($form, $form_state, $user_role, $password_constraint);

    $form['#title'] = $this->t('Edit %label constraint', ['%label' => $this->constraint->name()]);
    $form['actions']['submit']['#value'] = $this->t('Update constraint');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePasswordConstraint($password_constraint): PasswordConstraintInterface {
    // Received is the old, to-be-updated password constraint (loaded during URL
    // parameter conversion), but need to return the object that is _associated_
    // with the policy (because the constraint is not saved by itself, but it
    // gets saved along with the policy).
    return $this->policy->getConstraint($password_constraint->getUuid());
  }

}
