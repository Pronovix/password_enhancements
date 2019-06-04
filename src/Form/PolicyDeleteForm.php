<?php

namespace Drupal\password_enhancements\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds delete form for the password policy config entity.
 */
class PolicyDeleteForm extends EntityDeleteForm {

  /**
   * Role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * Constructs the policy delete form.
   *
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   Role storage.
   */
  public function __construct(RoleStorageInterface $role_storage) {
    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('user_role')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to delete the policy for the %role role?', [
      '%role' => $this->roleStorage->load($this->entity->getRole())->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDeletionMessage(): TranslatableMarkup {
    return $this->t('The policy for the %role role has been deleted.', [
      '%role' => $this->roleStorage->load($this->entity->getRole())->label(),
    ]);
  }

}
