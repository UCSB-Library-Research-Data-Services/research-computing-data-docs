<?php

namespace Drupal\simple_sitemap;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 */
class Logger {

  use StringTranslationTrait;

  /*
   * Can be debug/info/notice/warning/error.
   */
  protected const LOG_SEVERITY_LEVEL_DEFAULT = 'notice';

  /*
   * Can be status/warning/error.
   */
  protected const DISPLAY_MESSAGE_TYPE_DEFAULT = 'status';

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var string
   */
  protected $message = '';

  /**
   * @var array
   */
  protected $substitutions = [];

  /**
   * Logger constructor.
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   */
  public function __construct(
    LoggerInterface $logger,
    MessengerInterface $messenger,
    AccountProxyInterface $current_user
  ) {
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->currentUser = $current_user;
  }

  /**
   * @param $message
   * @param array $substitutions
   *
   * @return $this
   */
  public function m($message, array $substitutions = []): Logger {
    $this->message = $message;
    $this->substitutions = $substitutions;
    return $this;
  }

  /**
   * @param string $logSeverityLevel
   *
   * @return $this
   */
  public function log(string $logSeverityLevel = self::LOG_SEVERITY_LEVEL_DEFAULT): Logger {
    $this->logger->$logSeverityLevel(strtr($this->message, $this->substitutions));

    return $this;
  }

  /**
   * @param string $displayMessageType
   * @param string $permission
   *
   * @return $this
   */
  public function display(string $displayMessageType = self::DISPLAY_MESSAGE_TYPE_DEFAULT, string $permission = ''): Logger {
    if (empty($permission) || $this->currentUser->hasPermission($permission)) {
      $this->messenger->addMessage($this->t($this->message, $this->substitutions), $displayMessageType);
    }

    return $this;
  }
}
