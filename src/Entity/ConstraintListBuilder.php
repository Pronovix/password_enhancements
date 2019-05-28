<?php

namespace Drupal\password_enhancements\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
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
   * Password policy entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $policyStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityStorageInterface $policy_storage, Request $request) {
    parent::__construct($entity_type, $storage);
    $this->policyStorage = $policy_storage;
    $this->currentRequest = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('password_enhancements_policy'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    return $this->storage->loadByProperties([
      'policy' => $this->currentRequest->get('password_enhancements_policy'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#title'] = t('%name password policy constraints', [
      '%name' => $this->policyStorage->load($this->currentRequest->get('password_enhancements_policy'))->getName(),
    ]);
    $build['table']['#empty'] = $this->t('There is no password constraint created yet for this policy.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row = [
      'type' => $this->t('Type'),
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
  public function buildRow(EntityInterface $entity) {
    $settings = '';
    $plugin_settings = $entity->getSettings();
    if (!empty($plugin_settings)) {
      $settings .= '<ul>';
      foreach ($plugin_settings as $setting => $value) {
        if ($value !== NULL) {
          $settings .= "<li>{$setting}: {$value}</li>";
        }
      }
      $settings .= '</ul>';
    }
    else {
      $settings = $this->t('No settings available for this constraint.');
    }

    $row = [
      $entity->getType(),
      $entity->getDescriptionSingular(),
      $entity->getDescriptionPlural(),
      ['data' => ['#markup' => $settings]],
      $entity->status() ? $this->t('enabled') : $this->t('disabled'),
    ];

    return $row + parent::buildRow($entity);
  }

}
