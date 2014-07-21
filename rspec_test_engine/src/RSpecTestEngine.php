<?php

final class RSpecTestEngine extends ArcanistBaseUnitTestEngine {

  public function run() {
    $output = new TempFile();

    $future = new ExecFuture('rspec -f json -o '. $output .' -f progress');

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      sleep(0.5);
    } while (!$future->isReady());

    $future->resolve();

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
      $result->setName($example['full_description']);

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
      }

      $results[] = $result;
    }

    return $results;
  }
}
