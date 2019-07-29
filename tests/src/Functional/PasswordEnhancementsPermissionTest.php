<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\password_enhancements\Entity\Constraint;
use Drupal\password_enhancements\Entity\Policy;
use Drupal\user\Entity\Role;

/**
 * Developer app entity permission test.
 *
 * @group password_enhancements
 * @group password_enhancements_permissions
 */
class PasswordEnhancementsPermissionTest extends PasswordEnhancementsFunctionalTestBase {

  /**
   * Default user with no permission to change password enhancements settings.
   *
   * @var \Drupal\user\Entity\User
   */
  private $defaultUser;

  /**
   * Evaluated user with permission to change password enhancements settings.
   *
   * @var \Drupal\user\Entity\User
   */
  private $evaluatedUser;

  /**
   * The role.
   *
   * @var \Drupal\user\Entity\Role
   */
  protected $role;

  /**
   * The policy.
   *
   * @var \Drupal\password_enhancements\Entity\Policy
   */
  protected $policy;

  /**
   * The constraint.
   *
   * @var \Drupal\password_enhancements\Entity\Constraint
   */
  protected $constraint;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'password_enhancements',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->defaultUser = $this->createUser([
      'access content',
    ]);

    $this->evaluatedUser = $this->createUser([
      'administer user password enhancements settings',
    ]);

    $this->role = Role::create([
      'id' => 'test_role',
    ]);
    $this->role->save();

    $this->policy = Policy::create([
      'role' => $this->role->id(),
    ]);
    $this->policy->save();

    $this->constraint = Constraint::create([
      'id' => $this->randomMachineName(),
      'type' => 'special_character',
      'policy' => $this->policy->id(),
    ]);
    $this->constraint->save();
  }

  /**
   * Retrieve the entity link templates from an entity.
   *
   * @param string $entity_id
   *  The entity ID.
   *
   * @return array
   */
  protected function entityRoutes(string $entity_id): array {
    $entityType = $this->container->get('entity_type.manager')
      ->getDefinition($entity_id);
    $entityRoutes = array_keys($entityType->get('links'));

    return $entityRoutes;
  }

  /**
   * Testing the access to the policy and constraint links.
   */
  public function testLinks() {
    $this->drupalLogin($this->evaluatedUser);
    $this->accessPages('policy', 'password_enhancements_policy', 200);
    $this->accessPages('constraint', 'password_enhancements_constraint', 200);
    $this->drupalLogout();

    $this->drupalLogin($this->defaultUser);
    $this->accessPages('policy', 'password_enhancements_policy', 403);
    $this->accessPages('constraint', 'password_enhancements_constraint', 403);
    $this->drupalLogout();
  }

  /**
   * Accessing the entity links with a specified user.
   *
   * @param string $entity
   *  The entity variable.
   * @param string $entity_id
   *  The entity ID.
   * @param int $expected_status_code
   *  The expected status code.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function accessPages(string $entity, string $entity_id, int $expected_status_code) {
    $entityRoutes = $this->entityRoutes($entity_id);
    foreach ($entityRoutes as $rel) {
      $url = $this->$entity->toUrl($rel)->toString();
      $this->drupalGet($url);
      $status_code = $this->getSession()->getStatusCode();
      $this->assertEquals($expected_status_code, $status_code, "Got HTTP {$status_code}, expected HTTP {$expected_status_code}.");
    }
  }

}
