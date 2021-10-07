<?php /** @noinspection SpellCheckingInspection */

namespace Drupal\newrelic_log;

use Drupal\Core\DestructableInterface;
use Monolog\Handler\{BufferHandler as MonologBufferHandler,HandlerInterface};

class BufferHandler extends MonologBufferHandler implements DestructableInterface {

  /**
   * @return \Monolog\Handler\HandlerInterface
   */
  private function getHandler(): HandlerInterface {
    return $this->handler;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(array $record): bool {
    if ($record['level'] < $this->level) {
      return false;
    }

    if (!$this->initialized) {
      $this->initialized = true;
    }

    if ($this->bufferLimit > 0 && $this->bufferSize === $this->bufferLimit) {
      if ($this->flushOnOverflow) {
        $this->flush();
      } else {
        array_shift($this->buffer);
        $this->bufferSize--;
      }
    }

    if ($this->processors) {
      $record = $this->processRecord($record);
    }

    $this->buffer[] = $record;
    $this->bufferSize++;

    return false === $this->bubble;
  }


  public function destruct() {
    if($this->getHandler() instanceof Handler && $this->getHandler()->isConfigured()) {
      $this->close();
    }
  }
}
