<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Tests\password_enhancements\Traits\PasswordEnhancementsFunctionalTestTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Defines base for the functional tests.
 */
abstract class PasswordEnhancementsFunctionalTestBase extends BrowserTestBase {

  use PasswordEnhancementsFunctionalTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
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
  }

}
