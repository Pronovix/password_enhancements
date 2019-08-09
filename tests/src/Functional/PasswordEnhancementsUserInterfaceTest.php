<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\password_enhancements\Entity\Constraint;
use Drupal\password_enhancements\Entity\Policy;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;


/**
 * Password Enhancements module test.
 *
 * @group password_enhancements
 * @group password_enhancements_permissions
 */
class PasswordEnhancementsUserInterfaceTest extends PasswordEnhancementsFunctionalTestBase {

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
   * The constraint.
   *
   * @var \Drupal\password_enhancements\Entity\Constraint
   */
  protected $constraint;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
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
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    parent::tearDown();
  }

  /**
   * Tests the settings save.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testSettingsSave() {
    $this->drupalLogin($this->evaluatedUser);

    $this->drupalGet('admin/config/people/password-enhancements');
    $page = $this->getSession()->getPage();

    $page->selectFieldOption('edit-constraint-update-effect', 'strikethrough');
    $page->pressButton('Save configuration');
    $this->assertStatusMessage($this->t('The configuration options have been saved.'), Messenger::TYPE_STATUS);
  }

  /**
   * Tests if user is redirected to the password reset page after login.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testRequirePasswordReset() {
    $this->drupalLogin($this->evaluatedUser);

    $this->drupalGet('admin/config/people/password-enhancements');
    $page = $this->getSession()->getPage();

    $page->checkField('edit-require-password-change');
    $page->pressButton('Save configuration');
    $this->assertStatusMessage($this->t('The configuration options have been saved.'), Messenger::TYPE_STATUS);

    $this->drupalGet('admin/people/create');
    $page = $this->getSession()->getPage();

    $name = 'test_user_for_password_change';
    $page->selectFieldOption('edit-roles-password-enhancements-test-role', $this->role->id());
    $page->fillField('edit-name', $name);
    $page->fillField('edit-pass-pass1', 'userpass');
    $page->fillField('edit-pass-pass2', 'userpass');
    $page->pressButton('Create new account');

    $user = user_load_by_name($name);

    $this->drupalLogout();

    $this->drupalGet(user_pass_reset_url($user));

    $page->pressButton('Log in');

    $this->assertStatusMessage($this->t('Please change your password.'), Messenger::TYPE_STATUS);

  }

  /**
   * Tests user creations with specific constraints.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testConstraints() {
    $this->drupalLogin($this->evaluatedUser);

    $policy = Policy::create([
      'role' => $this->role->id(),
      'minimumRequiredConstraints' => 1,
      'expireSeconds' => 0,
      'expireWarnSeconds' => 0,
      'expiryWarningMessage' => 'Warning message for expiration',
      'priority' => 1,
      'status' => 'enabled',
    ]);
    $policy->save();

    // Create uppercase constraint
    $this->createConstraint($policy, 'lower_case', 1, 'Add at least one lower-cased letter.', 'Add @minimum_characters more lower-cased letters.', [
      'minimum_characters' => 3,
    ]);

    // Create users
    $this->drupalGet('admin/people/create');
    $page = $this->getSession()->getPage();

    // Create a new user where the password meets the lower-case requirements
    $this->createPasswordEnhancementUser($page, 'tEStPASSwORD', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any lower-case letters
    $this->createPasswordEnhancementUser($page, 'TESTPASSWORD', 'Add 3 more lower-cased letters.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one lower-case letter
    $this->createPasswordEnhancementUser($page, 'teSTPASSWORD', 'Add at least one lower-cased letter.', Messenger::TYPE_ERROR);

    $this->deleteConstraint($policy);

    // Create upper case constraint
    $this->createConstraint($policy, 'upper_case', 1, 'Add at least one upper-cased letter.', 'Add @minimum_characters more upper-cased letters.', [
      'minimum_characters' => 3,
    ]);

    // Create users
    $this->drupalGet('admin/people/create');
    $page = $this->getSession()->getPage();

    // Create a new user where the password meets the upper-case requirements
    $this->createPasswordEnhancementUser($page, 'TesTpaSsword', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any upper-case letters
    $this->createPasswordEnhancementUser($page, 'testpassword', 'Add 3 more upper-cased letters.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one upper-case letter
    $this->createPasswordEnhancementUser($page, 'TEstpassword', 'Add at least one upper-cased letter.', Messenger::TYPE_ERROR);

    $this->deleteConstraint($policy);

    // Create number constraint
    $this->createConstraint($policy, 'number', 1, 'Add at least one number.', 'Add @minimum_characters more numbers.', [
      'minimum_characters' => 3,
    ]);

    // Create users
    $this->drupalGet('admin/people/create');
    $page = $this->getSession()->getPage();

    // Create a new user where the password meets the number requirements
    $this->createPasswordEnhancementUser($page, 'testpassword123', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any numbers
    $this->createPasswordEnhancementUser($page, 'testpassword', 'Add 3 more numbers.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one number
    $this->createPasswordEnhancementUser($page, 'testpassword12', 'Add at least one number.', Messenger::TYPE_ERROR);

    $this->deleteConstraint($policy);

    // Create special character constraint
    $this->createConstraint($policy, 'special_character', 1, 'Add at least one special character.', 'Add @minimum_characters more special characters.', [
      'minimum_characters' => 3,
      'use_custom_special_characters' => 1,
      'special_characters' => '&%@$;+',
    ]);

    // Create users
    $this->drupalGet('admin/people/create');
    $page = $this->getSession()->getPage();

    // Create a new user where the password meets the special character requirements
    $this->createPasswordEnhancementUser($page, 'tes&tpas;swor+d', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password doesn't have any special characters
    $this->createPasswordEnhancementUser($page, 'testpassword', 'Add 3 more special characters.', Messenger::TYPE_ERROR);

    // Create a new user where the password needs at least one special character
    $this->createPasswordEnhancementUser($page, 'tes&tpas;sword', 'Add at least one special character.', Messenger::TYPE_ERROR);

  }

  /**
   * Tests the minimum required constraints setting.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testMinimumRequiredConstraint() {
    $this->drupalLogin($this->evaluatedUser);

    // The minimum required constraints value will overrule the constraint's requirement value
    $policy = Policy::create([
      'role' => $this->role->id(),
      'minimumRequiredConstraints' => 3,
      'expireSeconds' => 0,
      'expireWarnSeconds' => 0,
      'expiryWarningMessage' => 'Warning message for expiration',
      'priority' => 1,
      'status' => 'enabled',
    ]);
    $policy->save();

    $url = $policy->toUrl('collection')->toString();
    $this->drupalGet($url);
    $page = $this->getSession()->getPage();

    // Create uppercase constraint
    $this->createConstraint($policy, 'lower_case', 1, 'Add at least one lower-cased letter.', 'Add @minimum_characters more lower-cased letters.', [
      'minimum_characters' => 3,
    ]);

    // Create upper case constraint
    $this->createConstraint($policy, 'upper_case', 1, 'Add at least one upper-cased letter.', 'Add @minimum_characters more upper-cased letters.', [
      'minimum_characters' => 3,
    ]);

    // Create number constraint
    $this->createConstraint($policy, 'number', 0, 'Add at least one number.', 'Add @minimum_characters more numbers.', [
      'minimum_characters' => 1,
    ]);

    $url = $this->constraint->toUrl('collection')->toString();
    $this->drupalGet($url);
    $page = $this->getSession()->getPage();

    // Create users
    $this->drupalGet('admin/people/create');
    $page = $this->getSession()->getPage();

    // Create a new user where the password requirements are met
    $this->createPasswordEnhancementUser($page, 'passWORD1', 'Created a new user account for', Messenger::TYPE_STATUS);

    // Create a new user where the password requirements are not met
    $this->createPasswordEnhancementUser($page, 'passWORD', 'Add at least one number.', Messenger::TYPE_ERROR);

    // Create a new user where the password requirements are not met
    $this->createPasswordEnhancementUser($page, 'password1', 'Add 3 more upper-cased letters.', Messenger::TYPE_ERROR);

    // Create a new user where the password requirements are not met
    $this->createPasswordEnhancementUser($page, 'password', 'Add 3 more upper-cased letters.', Messenger::TYPE_ERROR);
  }

  /**
   * Creates a user.
   *
   * @param $policy
   * @param $page
   * @param string $password
   * @param string $expected_message
   * @param $message_type
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   */
  protected function createPasswordEnhancementUser($page, string $password, string $expected_message, $message_type) {
    $page->selectFieldOption('edit-roles-password-enhancements-test-role', $this->role->id());
    $page->fillField('edit-name', $this->randomMachineName(8));
    $page->fillField('edit-pass-pass1', $password);
    $page->fillField('edit-pass-pass2', $password);
    $page->pressButton('Create new account');
    $this->assertStatusMessage($this->t($expected_message), $message_type);
  }

  /**
   * Creates a constraint.
   *
   * @param $policy
   * @param string $type
   * @param int $required
   * @param string $description_singular
   * @param string $description_plural
   * @param array $settings
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createConstraint($policy, string $type, int $required, string $description_singular, string $description_plural, array $settings): void {
    $this->constraint = Constraint::create([
      'id' => $this->randomMachineName(),
      'type' => $type,
      'required' => $required,
      'policy' => $policy->id(),
      'descriptionSingular' => $description_singular,
      'descriptionPlural' => $description_plural,
      'status' => 'enabled',
      'settings' => $settings,
    ]);
    $this->constraint->save();
  }

  /**
   * Deletes a constraint.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function deleteConstraint($policy) {
    $this->drupalGet($policy->toUrl('collection')->toString());
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage constraints');
    $page->clickLink('Delete');
    $page->pressButton('Delete');
  }

}
