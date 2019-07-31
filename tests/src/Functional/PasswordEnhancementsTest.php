<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\user\Entity\Role;

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
   * The role.
   *
   * @var \Drupal\user\Entity\Role
   */
  protected $role;

  /**
   * The illegal role.
   *
   * @var \Drupal\user\Entity\Role
   */
  protected $illegal_role;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->evaluatedUser = $this->createUser([
      'administer user password enhancements settings',
    ]);

    $this->role = Role::create([
      'id' => 'test_role',
      'label' => 'Test role',
    ]);
    $this->role->save();

    $this->illegal_role = Role::create([
      'id' => 'illegal_role',
    ]);
    $this->illegal_role->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    parent::tearDown();
  }

  public function testCreatePolicy() {
    $this->drupalLogin($this->evaluatedUser);

    // Authenticated user, priority 0
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.add_form')->toString());
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('edit-role', AccountProxyInterface::AUTHENTICATED_ROLE);
    $page->fillField('edit-minimumrequiredconstraints', 3);
    $page->pressButton('Save');

    $authenticated_role = $this->container->get('entity_type.manager')->getStorage('user_role')->load(AccountProxyInterface::AUTHENTICATED_ROLE);
    // Check status message.
    $authenticated_role_label = $authenticated_role->label();
    $this->assertStatusMessage($this->t('Policy for the %role role has been successfully created.', [
      '%role' => $authenticated_role_label,
    ]), Messenger::TYPE_STATUS);

    // Check column values.
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=1]', $authenticated_role_label);
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=2]', 3);
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=3]', 'never expires');
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=4]', 0);

    // Created role
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.add_form')->toString());
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('edit-role', $this->role->id());
    $page->fillField('edit-minimumrequiredconstraints', 2);
    $page->pressButton('Save');

    $this->assertStatusMessage($this->t('Policy for the %role role has been successfully created.', [
      '%role' => $this->role->label(),
    ]), Messenger::TYPE_STATUS);

    // Check column values.
    $this->assertFieldByXPath('//table//tbody//tr[position()=2]//td[position()=1]', $this->role->label());
    $this->assertFieldByXPath('//table//tbody//tr[position()=2]//td[position()=2]', 2);
    $this->assertFieldByXPath('//table//tbody//tr[position()=2]//td[position()=3]', 'never expires');
    $this->assertFieldByXPath('//table//tbody//tr[position()=2]//td[position()=4]', 1);

    // Test an illegal choice (label missing)
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.add_form')->toString());
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('edit-role', $this->illegal_role->id());
    $page->fillField('edit-minimumrequiredconstraints', 3);
    $page->pressButton('Save');

    $role = $this->container->get('entity_type.manager')->getStorage('user_role')->load($this->illegal_role->id());
    // Check status message.
    $role_label = $role->label();
    $this->assertStatusMessage($this->t('An illegal choice has been detected. Please contact the site administrator.', [
      '%role' => $role_label,
    ]), Messenger::TYPE_ERROR);
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
