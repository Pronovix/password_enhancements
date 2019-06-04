<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * List builder for the password constraint config entities.
 */
class ConstraintListBuilder extends EntityListBuilder {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Password constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $passwordConstraintPluginManager;

  /**
   * Role storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $roleStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityStorageInterface $role_storage, PasswordConstraintPluginManager $password_constraint_plugin_manager, Request $request) {
    parent::__construct($entity_type, $storage);
    $this->currentRequest = $request;
    $this->passwordConstraintPluginManager = $password_constraint_plugin_manager;
    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): ConstraintListBuilder {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('plugin.manager.password_constraint'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load(): array {
    return $this->storage->loadByProperties([
      'policy' => $this->currentRequest->get('password_enhancements_policy')->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();
    $build['#title'] = $this->t('%name password policy constraints', [
      '%name' => $this->roleStorage->load($this->currentRequest->get('password_enhancements_policy')->getRole())->label(),
    ]);
    $build['table']['#empty'] = $this->t('There is no constraint created yet for this policy.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $row = [
      'constraint' => $this->t('Constraint'),
      'required' => $this->t('Required'),
      'description_singular' => $this->t('Description (singular)'),
      'description_plural' => $this->t('Description (plural)'),
      'settings' => $this->t('Settings'),
      'status' => $this->t('Status'),
      'operations' => $this->t('Operations'),
    ];

    return $row + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $settings = [];
    $plugin_settings = $entity->getSettings();
    if (!empty($plugin_settings)) {
      foreach ($plugin_settings as $setting => $value) {
        if ($value !== NULL) {
          $settings[] = "{$setting}: {$value}";
        }
      }
    }

    $plugin_definition = $this->passwordConstraintPluginManager->getDefinition($entity->getType());
    $row = [
      'constraint' => $plugin_definition['name'],
      'required' => $entity->isRequired() ? $this->t('yes') : $this->t('no'),
      'description_singular' => $entity->getDescriptionSingular(),
      'description_plural' => $entity->getDescriptionPlural(),
      'settings' => [
        'data' => [
          '#theme' => 'item_list',
          '#items' => $settings,
          '#empty' => $this->t('No settings available for this constraint.'),
        ],
      ],
      'status' => $entity->status() ? $this->t('enabled') : $this->t('disabled'),
    ];

    return $row + parent::buildRow($entity);
  }

}
