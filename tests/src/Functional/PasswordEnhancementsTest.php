<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\Role;
use Behat\Mink\Element\DocumentElement;
use Drupal\Core\Messenger\Messenger;

/**
 * Password Enhancements module test.
 *
 * @group password_enhancements
 * @group password_enhancements_permissions
 */
class PasswordEnhancementsTest extends PasswordEnhancementsFunctionalTestBase {

  /**
   * Evaluated user with permission to change settings.
   *
   * @var \Drupal\user\Entity\User
   */
  private $evaluatedUser;

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

    $this->evaluatedUser = $this->createUser([
      'administer user password enhancements settings',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    parent::tearDown();
  }

  public function testCreatePolicy() {
    $this->drupalLogin($this->evaluatedUser);
    $role = $this->container->get('entity_type.manager')->getStorage('user_role')->load(AccountProxyInterface::AUTHENTICATED_ROLE);
    $page = $this->getSession()->getPage();
    $role_label = $role->label();
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.add_form')->toString());
    $page->selectFieldOption('edit-role', AccountProxyInterface::AUTHENTICATED_ROLE);
    $page->fillField('edit-minimumrequiredconstraints', 3);
    $page->pressButton('Save');
  }

  /**
   * Tests create, edit or delete a policy.
   */
  public function _testPolicyAdministration() {

    // TODO: Create a policy

    // TODO: Edit a policy

    // TODO: Check field values

    // TODO: Set and save values

    // TODO: Check if values are changed

    // TODO: Delete a policy
  }

  /**
   * Tests create, edit or delete constraints.
   */
  public function _testConstraintAdministration() {
    // TODO: Create a constraint

    // TODO: Edit a constraint

    // TODO: Check field values

    // TODO: Set and save values

    // TODO: Check if values are changed

    // TODO: Delete a constraint
  }

  /**
   * Tests policy priorities.
   */
  public function _testPolicyPriorities() {

  }

  /**
   * Tests settings administration.
   */
  public function _testSettingsAdministration() {
    // TODO: Assure only valid data is accepted

    // TODO: Save configuration

    // TODO: Check if the values are changed
  }

}
