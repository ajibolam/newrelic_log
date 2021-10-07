# New Relic Log Drupal Module
This module ships logs from drupal to New Relic using its [Log API](https://docs.newrelic.com/docs/logs/log-management/log-api/introduction-log-api/).

It is non-blocking by sending the logs after the response has been sent (e.g. to the browser).  