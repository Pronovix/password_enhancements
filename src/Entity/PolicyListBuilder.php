<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\password_enhancements\Entity\Storage\PolicyEntityStorageInterface;
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
  public function __construct(EntityTypeInterface $entity_type, PolicyEntityStorageInterface $storage, RoleStorageInterface $role_storage) {
    parent::__construct($entity_type, $storage);

    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): PolicyListBuilder {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('user_role')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load(): array {
    $entity_ids = $this->storage->getQuery()->sort('priority', 'asc')->execute();
    return $this->storage->loadMultiple($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There is no policy created yet.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header = [
      'policy' => $this->t('Policy'),
      'minimum_required_constraints' => $this->t('Minimum required constraints'),
      'expire_days' => $this->t('Password expiry'),
      'priority' => $this->t('Priority'),
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['manage_constraints'] = [
      'type' => 'link',
      'title' => $this->t('Manage constraints'),
      'url' => Url::fromRoute('entity.password_enhancements_constraint.collection', [
        'password_enhancements_policy' => $entity->id(),
      ]),
      '#weight' => -100,
    ];
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\password_enhancements\Entity\PolicyInterface $entity */
    $expire_days = $entity->getExpireDays();

    $row = [
      'policy' => $this->roleStorage->load($entity->getRole())->label(),
      'minimum_required_constraints' => $entity->getMinimumRequiredConstraints(),
      'expire' => $expire_days > 0 ? $this->formatPlural($expire_days, 'after 1 day', 'after @count days') : $this->t('never expires'),
      'priority' => $entity->getPriority(),
    ];

    return $row + parent::buildRow($entity);
  }

}
