<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\password_enhancements\Entity\Constraint;
use Drupal\password_enhancements\Entity\Policy;
use Drupal\user\Entity\Role;


/**
 * Password Enhancements module test.
 *
 * @group password_enhancements
 * @group password_enhancements_permissions
 */
class PasswordEnhancementsUserInterfaceTest extends PasswordEnhancementsFunctionalTestBase
{

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
  protected function setUp()
  {
    parent::setUp();

    $this->evaluatedUser = $this->createUser([
      'administer user password enhancements settings',
      'administer users',
      'administer permissions',
    ]);

    $this->role = Role::create([
      'id' => 'password_enhancements_test_role',
      'label' => 'Password Enhancements Test Role',
    ]);
    $this->role->save();

    $this->policy = Policy::create([
      'role' => $this->role->id(),
      'minimumRequiredConstraints' => 1,
      'expireSeconds' => 0,
      'expireWarnSeconds' => 0,
      'expiryWarningMessage' => 'Warning message for expiration',
      'priority' => 1,
      'status' => 'enabled',
    ]);
    $this->policy->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown()
  {
    parent::tearDown();
  }

  public function testLowerCaseConstraint()
  {
    $this->drupalLogin($this->evaluatedUser);

    // Create uppercase constraint
    $this->drupalGet($this->policy->toUrl('collection')->toString());
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage constraints');
    $this->createConstraint('lower_case', 'Add at least one lower-cased letter.', 'Add @minimum_characters more lower-cased letters.', [
      'minimum_characters' => 3,
    ]);
    $this->drupalGet($this->constraint->toUrl('collection')->toString());
    $this->drupalGet('admin/people/create');

    // Create a new user where the password meets the lower-case requirements
    $this->createPasswordEnhancementUser($page, 'tEStPASSwORD', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any lower-case letters
    $this->createPasswordEnhancementUser($page, 'TESTPASSWORD', 'Add 3 more lower-cased letters.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one lower-case letter
    $this->createPasswordEnhancementUser($page, 'teSTPASSWORD', 'Add at least one lower-cased letter.', Messenger::TYPE_ERROR);

    $this->deleteConstraint();

    // Create lower case constraint
    $this->drupalGet($this->policy->toUrl('collection')->toString());
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage constraints');
    $this->createConstraint('upper_case', 'Add at least one upper-cased letter.', 'Add @minimum_characters more upper-cased letters.', [
      'minimum_characters' => 3,
    ]);
    $this->drupalGet($this->constraint->toUrl('collection')->toString());
    $this->drupalGet('admin/people/create');

    // Create a new user where the password meets the upper-case requirements
    $this->createPasswordEnhancementUser($page, 'TesTpaSsword', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any upper-case letters
    $this->createPasswordEnhancementUser($page, 'testpassword', 'Add 3 more upper-cased letters.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one upper-case letter
    $this->createPasswordEnhancementUser($page, 'TEstpassword', 'Add at least one upper-cased letter.', Messenger::TYPE_ERROR);

    $this->deleteConstraint();

    // Create number constraint
    $this->drupalGet($this->policy->toUrl('collection')->toString());
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage constraints');
    $this->createConstraint('number', 'Add at least one number.', 'Add @minimum_characters more numbers.', [
      'minimum_characters' => 3,
    ]);
    $this->drupalGet($this->constraint->toUrl('collection')->toString());
    $this->drupalGet('admin/people/create');

    // Create a new user where the password meets the number requirements
    $this->createPasswordEnhancementUser($page, 'testpassword123', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any numbers
    $this->createPasswordEnhancementUser($page, 'testpassword', 'Add 3 more numbers.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one number
    $this->createPasswordEnhancementUser($page, 'testpassword12', 'Add at least one number.', Messenger::TYPE_ERROR);

    $this->deleteConstraint();

    // Create special character constraint
    $this->drupalGet($this->policy->toUrl('collection')->toString());
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage constraints');
    $this->createConstraint('special_character', 'Add at least one special character.', 'Add @minimum_characters more special characters.', [
      'minimum_characters' => 3,
      'use_custom_special_characters' => 1,
      'special_characters' => '&%@$;+'
    ]);
    $this->drupalGet($this->constraint->toUrl('collection')->toString());
    $this->drupalGet('admin/people/create');

    // Create a new user where the password meets the special character requirements
    $this->createPasswordEnhancementUser($page, 'tes&tpas;swor+d', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any special characters
    $this->createPasswordEnhancementUser($page, 'testpassword', 'Add 3 more special characters.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one special character
    $this->createPasswordEnhancementUser($page, 'tes&tpas;sword', 'Add at least one special character.', Messenger::TYPE_ERROR);

  }

  protected function createPasswordEnhancementUser($page, string $password, string $expected_message, $message_type) {
    $page->selectFieldOption('edit-roles-password-enhancements-test-role', $this->role->id());
    $page->fillField('edit-name', $this->randomMachineName(8));
    $page->fillField('edit-pass-pass1', $password);
    $page->fillField('edit-pass-pass2', $password);
    $page->pressButton('Create new account');
    $this->assertStatusMessage($this->t($expected_message), $message_type);
  }

  protected function createConstraint(string $type, string $description_singular, string $description_plural, array $settings): void
  {
    $this->constraint = Constraint::create([
      'id' => $this->randomMachineName(),
      'type' => $type,
      'required' => 1,
      'policy' => $this->policy->id(),
      'descriptionSingular' => $description_singular,
      'descriptionPlural' => $description_plural,
      'status' => 'enabled',
      'settings' => $settings,
    ]);
    $this->constraint->save();
  }

  protected function deleteConstraint() {
    $this->drupalGet($this->policy->toUrl('collection')->toString());
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage constraints');
    $page->clickLink('Delete');
    $page->pressButton('Delete');
  }

}
