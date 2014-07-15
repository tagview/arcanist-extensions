<?php

final class TAPTestEngine extends ArcanistBaseUnitTestEngine {

  public function run() {
    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.tap.command');

    $future = new ExecFuture('%C', $command);

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      echo $stderr;
      sleep(0.5);
    } while (!$future->isReady());

    list($error, $stdout, $stderr) = $future->resolve();
    return $this->parseOutput($stdout);
  }

  public function shouldEchoTestResults() {
    return true;
  }

  private function parseOutput($output) {
    $results = array();

    foreach(explode(PHP_EOL, $output) as $line) {
      preg_match('/^(not ok|ok)\s+\d+\s+-?(.*)/', $line, $matches);
      if (count($matches) < 3) continue;

      $result = new ArcanistUnitTestResult();
      $result->setName(trim($matches[2]));

      if ($matches[1] == 'ok') {
        $result->setResult(ArcanistUnitTestResult::RESULT_PASS);
      } elseif ($matches[1] == 'not ok') {
        $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
      } else {
        continue;
      }

      $results[] = $result;
    }

    return $results;
  }
}
