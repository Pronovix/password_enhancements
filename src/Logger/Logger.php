<?php

namespace Drupal\password_enhancements\Logger;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\Utility\Error;
use Psr\Log\LoggerInterface;

/**
 * Defines module specific logger.
 */
final class Logger implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Constructs a new logger service.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger channel factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->loggerChannel = $logger_channel_factory->get('password_enhancements');
  }

  /**
   * Logs exception.
   *
   * @param string $subject
   *   The subject of the exception.
   * @param \Exception $exception
   *   The exception that needs to be logged.
   */
  public function logException(string $subject, \Exception $exception): void {
    $this->error('@subject<br> <br>%type: @message in %function (line %line of %file).<pre>@backtrace_string</pre>', Error::decodeException($exception) + [
      '@subject' => $subject,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    $this->loggerChannel->log($level, $message, $context);
  }

}
