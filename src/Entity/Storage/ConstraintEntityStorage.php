<?php

namespace Drupal\password_enhancements\Entity\Storage;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity storage for password constraint config entity.
 */
class ConstraintEntityStorage extends ConfigEntityStorage implements ConfigEntityStorageInterface {

  /**
   * Password constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $passwordConstraintPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, PasswordConstraintPluginManager $password_constraint_plugin_manager) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager);

    $this->passwordConstraintPluginManager = $password_constraint_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('plugin.manager.password_constraint')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    // Generate new ID for the entity based on the uniqueness of the selected
    // plugin.
    /** @var \Drupal\password_enhancements\Entity\ConstraintInterface $entity */
    $type = $entity->getType();
    if (empty($type)) {
      throw new ConstraintEntityStorageException('Plugin type cannot be empty.');
    }

    $plugin_definition = $this->passwordConstraintPluginManager->getDefinition($type);
    $entity->id = $type . '.' . $entity->getPolicy();
    if (!$plugin_definition['unique']) {
      $entity->id .= '.' . $this->uuidService->generate();
    }

    return parent::save($entity);
  }

}
