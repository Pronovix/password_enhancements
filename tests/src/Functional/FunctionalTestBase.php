<?php

namespace Drupal\Tests\password_enhancements\Functional;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Defines base for the functional tests.
 */
abstract class FunctionalTestBase extends WebDriverTestBase {

  /**
   * Translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * WebAssert object.
   *
   * @var \Drupal\Tests\WebAssert
   */
  protected $webAssert;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->translationManager = $this->container->get('string_translation');
    $this->webAssert = $this->assertSession();
  }

  /**
   * Assert Drupal status message.
   *
   * @param string $message
   *   The message that needs to be asserted.
   * @param string $type
   *   Type of the error message, it can be any of the
   *   \Drupal\Core\Messenger\Messenger::TYPE_* messenger types.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   */
  protected function assertStatusMessage(string $message, string $type): void {
    $this->webAssert->elementContains('css', "div[data-drupal-messages] > .messages--{$type}", $message);
  }

  /**
   * String translation shortcut.
   */
  protected function t(string $string, array $args = [], array $options = []): TranslatableMarkup {
    return $this->translationManager->translate($string, $args, $options);
  }

}
