<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\Role;
use Behat\Mink\Element\DocumentElement;

/**
 * Password Enhancements module test.
 *
 * @group password_enhancements
 * @group password_enhancements_permissions
 */
class PasswordEnhancementsTest extends PasswordEnhancementsFunctionalTestBase {

  /**
   * Admin user with permission to change setting.
   *
   * @var \Drupal\user\Entity\User
   */
  private $adminUser;

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

    $this->adminUser = $this->createUser([
      'administer user password enhancements settings',
    ]);

    $this->authenticatedUser = $this->createUser();

    $this->constraintStorage = $this->container->get('entity_type.manager')->getStorage('password_enhancements_constraint');
    $this->policyStorage = $this->container->get('entity_type.manager')->getStorage('password_enhancements_policy');
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    parent::tearDown();
  }

  /**
   * Tests access to the admin pages with admin/authenticated/anonymous roles.
   */
  public function testAccess() {
    // Test access with admin role.
    $this->drupalLogin($this->rootUser);
    $this->assertPaths(TRUE);
  }

  /**
   * Checks access to the admin pages.
   *
   * @param bool $access
   *   Whether the current user should access the pages or not.
   */
  protected function assertPaths(bool $access) {
    $expected_code = $access ? 200 : 403;

    $visit_path = function (string $path, array $query = []) use ($expected_code) {
      $options = [];
      if ($query) {
        $options['query'] = $query;
      }
      $this->drupalGetNoMetaRefresh($path, $options);
      $this->assertEquals($expected_code, $this->getSession()
        ->getStatusCode(), $path);
    };

    // Get all routes defined by the module and check every route that requires
    // the permission "administer user password enhancements settings".
    $module_path = $this->container->get('module_handler')
      ->getModule('password_enhancements')
      ->getPath();
    $discovery = new YamlDiscovery('routing', [
      'password_enhancements' => DRUPAL_ROOT . '/' . $module_path,
    ]);
    $module_routes = $discovery->findAll()['password_enhancements'];

    foreach ($module_routes as $route => $data) {
      // Check routes that require permission "administer user password enhancements settings".
      if (in_array('administer user password enhancements settings', $data['requirements'])) {
        $visit_path($data['path']);
      }
    }

    /*
     TODO: Check access to the rest of the routes (?)

     entity.password_enhancements_policy.add_form
     entity.password_enhancements_policy.collection,
     entity.password_enhancements_policy.edit_form
     entity.password_enhancements_policy.delete_form
     entity.password_enhancements_constraint.add_form
     entity.password_enhancements_constraint.collection
     entity.password_enhancements_constraint.edit_form
     entity.password_enhancements_constraint.delete_form
   */
  }

  /**
   * Tests create, edit or delete a policy.
   *
   */
  public function testPolicyAdministration() {
    $this->drupalLogin($this->adminUser);

    $role = $this->container->get('entity_type.manager')->getStorage('user_role')->load(AccountProxyInterface::AUTHENTICATED_ROLE);
    $page = $this->getSession()->getPage();

    // Create a policy
    $this->createPolicy($page, $role);

    // TODO: Edit a policy

    // TODO: Check field values

    // TODO: Set and save values

    // TODO: Check if values are changed

    // TODO: Delete a policy
  }

  /**
   * Helper function to create and test policy.
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *  The Document element.
   * @param \Drupal\user\Entity\Role $role
   *  The User role.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function createPolicy(DocumentElement $page, Role $role) {
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.add_form')->toString());
    $page->selectFieldOption('edit-role', AccountProxyInterface::AUTHENTICATED_ROLE);
    $page->fillField('edit-minimumrequiredconstraints', 3);
    $page->pressButton('Save');

    // TODO: Check status message

    // TODO: Check column values
  }

  /**
   * Tests create, edit or delete constraints.
   *
   */
  public function testConstraintAdministration() {
    // TODO: Create a constraint

    // TODO: Edit a constraint

    // TODO: Check field values

    // TODO: Set and save values

    // TODO: Check if values are changed

    // TODO: Delete a constraint
  }

  /**
   * Tests policy priorities.
   *
   */
  public function testPolicyPriorities() {

  }

  /**
   * Tests settings administration.
   *
   */
  public function testSettingsAdministration() {

    // TODO: Assure only valid data is accepted

    // TODO: Save configuration

    // TODO: Check if the values are changed

  }

}
