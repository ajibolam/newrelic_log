<?php

namespace Drupal\newrelic_log\Logger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\{RfcLogLevel, RfcLoggerTrait};
use Monolog\Logger;
use Psr\Log\LoggerInterface;


/**
 * Redirects logging messages to New Relic.
 */
class NewrelicLog implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * A configuration object containing newrelic_log settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * NewrelicLog constructor.
   * @param ConfigFactoryInterface $config_factory
   * @param LogMessageParserInterface $parser
   * @param LoggerInterface $logger
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LogMessageParserInterface $parser,
    LoggerInterface $logger
  ) {
    $this->config = $config_factory->get('newrelic_log.settings');
    $this->parser = $parser;
    /** @var Logger $logger */
    $this->logger = $logger;
    if($channel = $this->config->get('channel')) {
      $this->logger = $this->logger->withName($channel);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    // Populate the message placeholders and then replace them in the message.
    $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);
    $message = empty($message_placeholders) ? $message : strtr($message, $message_placeholders);

    $this->logger->addRecord($this->getMonologLoglevel($level), strip_tags($message), array_filter($context));
  }

  /**
   * @param $level
   * @return int
   */
  private function getMonologLoglevel($level): int
  {
    switch ($level) {
      case RfcLogLevel::ALERT:
        $error_type = Logger::ALERT;
        break;

      case RfcLogLevel::CRITICAL:
        $error_type = Logger::CRITICAL;
        break;

      case RfcLogLevel::EMERGENCY:
        $error_type = Logger::EMERGENCY;
        break;

      case RfcLogLevel::ERROR:
        $error_type = Logger::ERROR;
        break;

      case RfcLogLevel::WARNING:
        $error_type = Logger::WARNING;
        break;

      case RfcLogLevel::DEBUG:
        $error_type = Logger::DEBUG;
        break;

      case RfcLogLevel::INFO:
        $error_type = Logger::INFO;
        break;

      case RfcLogLevel::NOTICE:
        $error_type = Logger::NOTICE;
        break;

      default:
        $error_type = $level;
        break;
    }
    return $error_type;
  }
}
