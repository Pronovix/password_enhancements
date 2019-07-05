<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Behat\Mink\Element\DocumentElement;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\Role;

/**
 * Tests admin settings related functionality.
 */
class SettingsCase extends FunctionalTestBase {

  /**
   * Admin user with permission to change setting.
   *
   * @var \Drupal\user\Entity\User
   */
  private $adminUser;

  /**
   * Simple authenticated user without any extra permissions.
   *
   * @var \Drupal\user\Entity\User
   */
  private $authenticatedUser;

  /**
   * Constraint storage.
   *
   * @var \Drupal\password_enhancements\Entity\Storage\ConstraintEntityStorage
   */
  private $constraintStorage;

  /**
   * Policy storage.
   *
   * @var \Drupal\password_enhancements\Entity\Storage\PolicyEntityStorage
   */
  private $policyStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'password_enhancements',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Setup users.
    $this->adminUser = $this->createUser([
      'administer user password enhancements settings',
    ]);
    $this->authenticatedUser = $this->createUser();

    $this->constraintStorage = $this->container->get('entity_type.manager')->getStorage('password_enhancements_constraint');
    $this->policyStorage = $this->container->get('entity_type.manager')->getStorage('password_enhancements_policy');
  }

  /**
   * Helper function to create and test policy.
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Document element.
   * @param \Drupal\user\Entity\Role $role
   *   User role.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ElementHtmlException
   */
  private function createPolicy(DocumentElement $page, Role $role) {
    // Create policy.
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.add_form')->toString());
    $page->selectFieldOption('edit-role', AccountProxyInterface::AUTHENTICATED_ROLE);
    $page->fillField('edit-minimumrequiredconstraints', 3);
    $page->pressButton('Save');

    // Check status message.
    $role_label = $role->label();
    $this->assertStatusMessage($this->t('Policy for the %role role has been successfully created.', [
      '%role' => $role_label,
    ]), Messenger::TYPE_STATUS);

    // Check column values.
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=1]', $role_label);
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=2]', 3);
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=3]', 'not set');
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=4]', 0);
  }

  /**
   * Data provider for all settings routes.
   *
   * @return array
   *   Returns all of the available routes with their parameters.
   */
  public function dataRoutes() {
    // Define parameters for the routes.
    $policy_parameter = [
      'password_enhancements_policy' => NULL,
    ];
    $constraint_parameter = [
      'password_enhancements_policy' => NULL,
      'password_enhancements_constraint' => NULL,
    ];

    return [
      ['password_enhancements.settings', []],
      ['entity.password_enhancements_policy.add_form', []],
      ['entity.password_enhancements_policy.collection', []],
      ['entity.password_enhancements_policy.edit_form', $policy_parameter],
      ['entity.password_enhancements_policy.delete_form', $policy_parameter],
      ['entity.password_enhancements_constraint.add_form', $policy_parameter],
      ['entity.password_enhancements_constraint.collection', $policy_parameter],
      ['entity.password_enhancements_constraint.edit_form', $constraint_parameter],
      ['entity.password_enhancements_constraint.delete_form', $constraint_parameter],
    ];
  }

  /**
   * Tests if admin can create, edit or delete a policy.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testPolicyManagement(): void {
    // Login as admin.
    $this->drupalLogin($this->adminUser);

    /** @var \Drupal\user\Entity\Role $role */
    $role = $this->container->get('entity_type.manager')->getStorage('user_role')->load(AccountProxyInterface::AUTHENTICATED_ROLE);
    $page = $this->getSession()->getPage();
    $role_label = $role->label();

    // Create a new policy.
    $this->createPolicy($page, $role);

    // Edit a policy.
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.collection')->toString());
    $this->click('table tr:first-child .dropbutton .dropbutton-toggle button');
    $page->clickLink('Edit');

    // Check current fields' values.
    $this->assertOptionSelected('edit-role', AccountProxyInterface::AUTHENTICATED_ROLE);
    $this->assertFieldById('edit-minimumrequiredconstraints', 3);
    $this->assertNoFieldChecked('edit-expire-password');
    $this->assertFalse($page->findById('edit-expire-days')->isVisible());
    $this->assertFalse($page->findById('edit-expire-warn-before-days')->isVisible());
    $this->assertFalse($page->findById('edit-expirywarningmessage')->isVisible());

    // Set new value and save.
    $page->checkField('edit-expire-password');
    $page->fillField('edit-expire-days', 10);
    $page->fillField('edit-expire-warn-before-days', 5);
    $this->assertTrue($page->findById('edit-expirywarningmessage')->isVisible());
    $this->assertFieldById('edit-expirywarningmessage', 'Your password will expire on @date_time, please <a href="@url">change your password</a> before it expires to prevent any potential data loss.');
    $page->pressButton('edit-submit');
    $this->assertStatusMessage($this->t('Policy for the %role role has been successfully updated.', [
      '%role' => $role_label,
    ]), Messenger::TYPE_STATUS);

    // Check if value changed.
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=2]', 3);
    $this->assertFieldByXPath('//table//tbody//tr[position()=1]//td[position()=3]', 'after 10 days');

    // Delete policy.
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_policy.collection')->toString());
    $this->click('table tr:first-child .dropbutton .dropbutton-toggle button');
    $page->clickLink('Delete');
    $this->webAssert->elementContains('css', '.page-title', $this->t('Are you sure you want to delete the policy for the %role role?', [
      '%role' => $role_label,
    ]));
    $page->pressButton('Delete');
    $this->assertStatusMessage($this->t('The policy for the %role role has been deleted.', [
      '%role' => $role_label,
    ]), Messenger::TYPE_STATUS);
  }

  /**
   * Data provider for constraint types.
   *
   * @return array
   *   Constraint types.
   */
  public function dataConstraintTypes() {
    return [
      ['lower_case'],
      ['minimum_length'],
      ['number'],
      ['upper_case'],
      ['special_character'],
    ];
  }

  /**
   * Tests if admin can create, edit or delete a constraint.
   *
   * @param string $type
   *   The type of the constraint.
   *
   * @dataProvider dataConstraintTypes
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ElementHtmlException
   */
  public function testConstraintManagement(string $type): void {
    // Login as admin.
    $this->drupalLogin($this->adminUser);

    // Create a new policy to be able to create constraints.
    /** @var \Drupal\user\Entity\Role $role */
    $role = $this->container->get('entity_type.manager')->getStorage('user_role')->load(AccountProxyInterface::AUTHENTICATED_ROLE);
    $page = $this->getSession()->getPage();
    $this->createPolicy($page, $role);

    $url_parameter = ['password_enhancements_policy' => $role->id()];

    // Create new constraint.
    $page->clickLink($role->label());
    $this->assertUrl(Url::fromRoute('entity.password_enhancements_constraint.collection', $url_parameter));
    $this->drupalGet(Url::fromRoute('entity.password_enhancements_constraint.add_form', $url_parameter));
    $page->selectFieldOption('edit-type', $type);
    $this->assertSession()->assertWaitOnAjaxRequest();

    if ($type === 'minimum_length') {
      $page->checkField('edit-required');
      $min_length = 8;
    }
    else {
      $min_length = 2;
    }

    $singular_description = "Singular description for {$type}.";
    $page->fillField('edit-descriptionsingular', $singular_description);

    $plural_description = "Plural description for {$type}.";
    $page->fillField('edit-descriptionplural', $plural_description);

    $page->fillField('edit-settings-minimum-characters', $min_length);

    $page->pressButton('edit-button');
    $this->assertText('The constraint was successfully saved.');

    // Validate rows.
    $path = '//table//tbody//tr//td[position()=:position]';
    $this->assertFieldByXPath(strtr($path, [':position' => 1]), $type);
    $this->assertFieldByXPath(strtr($path, [':position' => 2]), $type === 'minimum_length' ? 'yes' : 'no');
    $this->assertFieldByXPath(strtr($path, [':position' => 3]), $singular_description);
    $this->assertFieldByXPath(strtr($path, [':position' => 4]), $plural_description);
    $this->assertFieldByXPath(strtr($path, [':position' => 5]), strtr('minimum_characters: :min_length', [
      ':min_length' => $min_length,
    ]));
    // @TODO: Currently it can be only 'enabled'.
    $this->assertFieldByXPath(strtr($path, [':position' => 6]), 'enabled');
  }

  /**
   * Tests user access for the admin settings pages.
   *
   * @param string $route
   *   Route to test.
   * @param array $parameters
   *   Parameters for the route.
   *
   * @dataProvider dataRoutes
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function xtestSettingsPageAccess(string $route, array $parameters): void {
    foreach ($parameters as $parameter => &$value) {
      switch ($parameter) {
        case 'password_enhancements_policy':
          // Grab a policy.
          $policies = $this->policyStorage->loadMultiple();
          $policy = reset($policies);
          $value = $policy->id();
          break;

        case 'password_enhancements_constraint':
          // Grab a constraint.
          $constraints = $this->constraintStorage->loadMultiple();
          $constraint = reset($constraints);
          $value = $constraint->id();
          break;
      }
    }
    unset($parameter, $value);

    $this->drupalLogin($this->authenticatedUser);
    $this->drupalGet(Url::fromRoute($route, $parameters));
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute($route, $parameters));
    $this->assertSession()->statusCodeEquals(200);
  }

}
