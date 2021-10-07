<?php /** @noinspection SpellCheckingInspection */

namespace Drupal\newrelic_log;

use Drupal\Core\Config\ConfigFactoryInterface;
use Monolog\Handler\{Curl, MissingExtensionException};
use Monolog\Logger;
use NewRelic\Monolog\Enricher\Handler as NewrelicEnricherHandler;

class Handler extends NewrelicEnricherHandler {

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   *
   * @throws MissingExtensionException If the curl extension is missing
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('newrelic_log.settings');
    parent::__construct($config->get('log_level') ?? Logger::DEBUG);
    $license = $config->get('license');
    $host = $config->get('host');
    if($this->licenseKey === "NO_LICENSE_KEY_FOUND" && $license) {
      $this->setLicenseKey($license);
    }

    if(is_null($this->host) && $host) {
      $this->setHost($host);
    }
  }

  public function isConfigured(): bool {
    if($this->licenseKey && $this->licenseKey != "NO_LICENSE_KEY_FOUND" && $this->host) {
      return true;
    }
    return false;
  }

  public function canConnect(): bool {
    if($this->isConfigured() === false){
      return false;
    }
    $ch = $this->getCurlHandler();
    $headers = array(
      'Content-Type: application/json',
      'X-License-Key: ' . $this->licenseKey
    );

    curl_setopt($ch, CURLOPT_POSTFIELDS, []);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = Curl\Util::execute($ch, 1, false);
    if($response === false) {
      return false;
    }
    if (empty(json_decode($response, true))) {
      return false;
    }
    return true;
  }
}
