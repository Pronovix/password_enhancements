<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * List builder for password policy config entities.
 */
class PolicyListBuilder extends EntityListBuilder {

  /**
   * Role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, RoleStorageInterface $role_storage) {
    parent::__construct($entity_type, $storage);

    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('user_role')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getStorage()->getQuery()->sort('priority', 'asc')->execute();
    return $this->storage->loadMultiple($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There is no password policy created yet.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'name' => $this->t('Name'),
      'role' => $this->t('Role'),
      'minimum_required_constraints' => $this->t('Minimum required constraints'),
      'expire_days' => $this->t('Password expiry'),
      'priority' => $this->t('Priority'),
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $expire_days = $entity->getExpireDays();

    $role = $entity->getRole();

    $row = [
      'name' => [
        'data' => [
          '#type' => 'link',
          '#title' => $entity->getName(),
          '#url' => Url::fromRoute('entity.password_enhancements_constraint.collection', [
            'password_enhancements_policy' => $entity->id(),
          ]),
        ],
      ],
      'role' => $this->roleStorage->load($role)->label(),
      'minimum_required_constraints' => $entity->getMinimumRequiredConstraints(),
      'expire' => $expire_days > 0 ? $this->formatPlural($expire_days, 'after 1 day', 'after @count days') : $this->t('not set'),
      'priority' => $entity->getPriority(),
    ];

    return $row + parent::buildRow($entity);
  }

}
