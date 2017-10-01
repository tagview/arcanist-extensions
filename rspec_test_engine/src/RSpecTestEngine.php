<?php

final class RSpecTestEngine extends ArcanistUnitTestEngine {

  public function run() {
    $output = new TempFile();

    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.rspec.command');
    if (!$command) $command = 'rspec';

    $future = new ExecFuture($command .' -f json -o '. $output .' -f progress');

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      sleep(0.5);
    } while (!$future->isReady());

    return $this->parseOutput(Filesystem::readFile($output));
  }

  public function shouldEchoTestResults() {
    return true;
  }

  private function parseOutput($output) {
    $results = array();

    $json = json_decode($output, true);

    foreach ($json['examples'] as $example) {
      $result = new ArcanistUnitTestResult();
      $result->setName(substr($example['full_description'], 0, 255));

      if (array_key_exists('run_time', $example)) {
        $result->setDuration($example['run_time']);
      }

      switch ($example['status']) {
        case 'passed':
          $result->setResult(ArcanistUnitTestResult::RESULT_PASS);
          break;

        case 'failed':
          $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
          $result->setUserData($example['exception']['message']);
          break;

        case 'pending':
          $result->setResult(ArcanistUnitTestResult::RESULT_SKIP);
          break;
      }

      $results[] = $result;
    }

    return $results;
  }
}
