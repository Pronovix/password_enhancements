<?php

namespace Drupal\Tests\password_enhancements\Traits;

trait PasswordEnhancementsFunctionalTestTrait {

  /**
   * Returns absolute URL starts with a slash.
   *
   * @param string $url
   *   The URL.
   *
   * @return string
   *   URL starts with a slash, if the URL is absolute.
   */
  protected static function fixUrl(string $url): string {
    if (strpos($url, 'http:') === 0 || strpos($url, 'https:') === 0) {
      return $url;
    }
    return (strpos($url, '/') === 0) ? $url : "/{$url}";
  }

}
