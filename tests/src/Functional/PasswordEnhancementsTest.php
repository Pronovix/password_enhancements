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

  /**
   * Tests create, edit or delete a policy.
   */
  public function testPolicyAdministration() {
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
  public function testPolicyPriorities() {

  }

  /**
   * Tests settings administration.
   */
  public function testSettingsAdministration() {
    // TODO: Assure only valid data is accepted

    // TODO: Save configuration

    // TODO: Check if the values are changed
  }

}
