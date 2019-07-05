<?php

namespace Drupal\password_enhancements\Entity\Storage;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\password_enhancements\Logger\Logger;
use Drupal\password_enhancements\PasswordConstraintPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity storage for password constraint config entity.
 */
class ConstraintEntityStorage extends ConfigEntityStorage implements ConstraintEntityStorageInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Drupal\password_enhancements\Logger\Logger
   */
  protected $logger;

  /**
   * Password constraint plugin manager.
   *
   * @var \Drupal\password_enhancements\PasswordConstraintPluginManager
   */
  protected $passwordConstraintPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, PasswordConstraintPluginManager $password_constraint_plugin_manager, MemoryCacheInterface $memory_cache, EntityTypeManagerInterface $entity_type_manager, Logger $logger) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager, $memory_cache);

    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->passwordConstraintPluginManager = $password_constraint_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): ConfigEntityStorageInterface {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('plugin.manager.password_constraint'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.manager'),
      $container->get('logger.password_enhancements')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity): int {
    // Generate new ID for the entity based on the uniqueness of the selected
    // plugin.
    /** @var \Drupal\password_enhancements\Entity\ConstraintInterface $entity */
    $type = $entity->getType();
    if (empty($type)) {
      throw new ConstraintEntityStorageException('Plugin type cannot be empty.');
    }

    $plugin_definition = $this->passwordConstraintPluginManager->getDefinition($type);
    $entity->id = $entity->getPolicy() . '.' . $type;
    if (!$plugin_definition['unique']) {
      $entity->id .= '.' . $this->uuidService->generate();
    }

    return parent::save($entity);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function loadByRole(string $role): array {
    return $this->loadByRoles([$role]);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function loadByRoles(array $roles): array {
    // Load password policies by their priority and constraints.
    /** @var \Drupal\password_enhancements\Entity\Storage\PolicyEntityStorageInterface $password_policy_storage */
    $password_policy_storage = $this->entityTypeManager->getStorage('password_enhancements_policy');
    $policies = $password_policy_storage->loadMultipleByRoleAndPriority($roles);

    // No policies available for the given role, return with an empty array.
    if (empty($policies)) {
      return [];
    }

    /** @var \Drupal\password_enhancements\Entity\Constraint[] $constraints */
    $constraints = $this->entityTypeManager->getStorage('password_enhancements_constraint')
      ->loadByProperties([
        'policy' => array_keys($policies),
      ]);

    // Get constrains ordered by the policy's priority.
    // If a constraint is defined in a higher priority policy then the
    // constraints defined on the lower priority will be overridden based on
    // their type.
    // Non-unique constrains from the same policy won't override each other,
    // although if a higher priority policy's constraint defines that specific
    // type then it will override each non-unique constraint from the lower
    // priority from the same type.
    $checked_constraints = [];
    $constraint_list = [];
    foreach ($policies as $policy) {
      $usable_constraints_by_policy = [];

      foreach ($constraints as $constraint_id => $constraint) {
        if ($constraint->getPolicy() === $policy->id()) {
          $type = $constraint->getType();

          if (!array_key_exists($type, $checked_constraints)) {
            $usable_constraints_by_policy[$type] = TRUE;
            $constraint_list[$constraint_id] = $constraint;
          }

          // Remove already checked constraint.
          unset($constraints[$constraint_id]);
        }
      }

      $checked_constraints += $usable_constraints_by_policy;
    }

    return $constraint_list;
  }

}
