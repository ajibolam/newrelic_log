<?php /** @noinspection SpellCheckingInspection */

namespace Drupal\newrelic_log;

use NewRelic\Monolog\Enricher\Formatter as NewrelicEnricherFormatter;

class Formatter extends NewrelicEnricherFormatter {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $batchMode = self::BATCH_MODE_JSON,
    $appendNewline = false
  ) {
    parent::__construct($batchMode, $appendNewline);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatBatchJson(array $records): string {
    $records = array_values($records);
    return parent::formatBatchJson($records);
  }
}
